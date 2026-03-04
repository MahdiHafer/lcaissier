<?php

namespace App\Http\Controllers;

use App\BonLivraison;
use App\Client;
use App\Facture;
use App\Product;
use App\StockMovement;
use App\Vente;
use App\VenteDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BonLivraisonController extends Controller
{
    public function index(Request $request)
    {
        $query = BonLivraison::with('client')->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%$search%")
                    ->orWhereHas('client', function ($qc) use ($search) {
                        $qc->where('nom', 'like', "%$search%")
                            ->orWhere('telephone', 'like', "%$search%");
                    });
            });
        }

        $bons = $query->paginate(20)->appends($request->all());
        return view('bons_livraison.index', compact('bons'));
    }

    public function create()
    {
        $clients = Client::orderBy('nom')->get();
        $products = Product::orderBy('marque')->get();

        return view('bons_livraison.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_bon' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
            'notes' => 'nullable|string',
            'designation' => 'required|array|min:1',
            'designation.*' => 'nullable|string|max:255',
            'product_id' => 'nullable|array',
            'product_id.*' => 'nullable|exists:products,id',
            'image' => 'nullable|array',
            'image.*' => 'nullable|string|max:255',
            'quantite' => 'required|array|min:1',
            'quantite.*' => 'required|integer|min:1',
            'prix_unitaire' => 'required|array|min:1',
            'prix_unitaire.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $bon = BonLivraison::create([
                'numero' => $this->generateNumero(),
                'date_bon' => $request->date_bon,
                'client_id' => $request->client_id,
                'notes' => $request->notes,
                'total' => 0,
                'status' => 'brouillon',
                'user_id' => auth()->id(),
            ]);

            $total = $this->syncDetails($bon, $request);
            $bon->update(['total' => $total]);
            $this->refreshBonStatus($bon);

            DB::commit();
            return redirect()->route('bons-livraison.index')->with('success', 'Bon de livraison cree avec succes.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur creation BL: ' . $e->getMessage());
        }
    }

    public function edit(BonLivraison $bons_livraison)
    {
        $bon = $bons_livraison->load('details');
        $clients = Client::orderBy('nom')->get();
        $products = Product::orderBy('marque')->get();

        return view('bons_livraison.edit', compact('bon', 'clients', 'products'));
    }

    public function update(Request $request, BonLivraison $bons_livraison)
    {
        if ($bons_livraison->details()->where('quantite_vendue', '>', 0)->exists()) {
            return back()->with('error', 'Ce BL contient deja des lignes vendues. Modification bloquee.');
        }

        $request->validate([
            'date_bon' => 'required|date',
            'client_id' => 'nullable|exists:clients,id',
            'notes' => 'nullable|string',
            'designation' => 'required|array|min:1',
            'designation.*' => 'nullable|string|max:255',
            'product_id' => 'nullable|array',
            'product_id.*' => 'nullable|exists:products,id',
            'image' => 'nullable|array',
            'image.*' => 'nullable|string|max:255',
            'quantite' => 'required|array|min:1',
            'quantite.*' => 'required|integer|min:1',
            'prix_unitaire' => 'required|array|min:1',
            'prix_unitaire.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $bons_livraison->update([
                'date_bon' => $request->date_bon,
                'client_id' => $request->client_id,
                'notes' => $request->notes,
            ]);

            $total = $this->syncDetails($bons_livraison, $request);
            $bons_livraison->update(['total' => $total]);
            $this->refreshBonStatus($bons_livraison);

            DB::commit();
            return redirect()->route('bons-livraison.index')->with('success', 'Bon de livraison modifie avec succes.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur modification BL: ' . $e->getMessage());
        }
    }

    public function destroy(BonLivraison $bons_livraison)
    {
        if ($bons_livraison->details()->where('quantite_vendue', '>', 0)->exists()) {
            return back()->with('error', 'BL deja converti en vente: suppression bloquee.');
        }

        $bons_livraison->delete();
        return back()->with('success', 'Bon de livraison supprime avec succes.');
    }

    public function print(BonLivraison $bon)
    {
        $bon->load('details', 'client');
        return view('bons_livraison.print', compact('bon'));
    }

    public function convertForm(BonLivraison $bon)
    {
        $bon->load('details.product', 'client');
        return view('bons_livraison.convert', compact('bon'));
    }

    public function convertToSale(Request $request, BonLivraison $bon)
    {
        $request->validate([
            'detail_ids' => 'required|array|min:1',
            'detail_ids.*' => 'required|integer',
            'sell_qty' => 'required|array',
            'sell_qty.*' => 'nullable|integer|min:0',
            'mode_paiement' => 'required|string',
            'remise' => 'nullable|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
            'tva_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $selectedLines = $bon->details()
            ->whereIn('id', $request->detail_ids)
            ->with('product')
            ->get();

        if ($selectedLines->isEmpty()) {
            return back()->with('error', 'Aucune ligne valide a convertir.');
        }

        DB::beginTransaction();
        try {
            $linesToSell = collect();

            foreach ($selectedLines as $line) {
                $sellQty = (int) ($request->sell_qty[$line->id] ?? 0);
                $remaining = max((int) $line->quantite - (int) $line->quantite_vendue, 0);

                if ($sellQty <= 0) {
                    continue;
                }
                if ($sellQty > $remaining) {
                    throw new \RuntimeException("Quantite invalide pour {$line->designation}. Reste: {$remaining}");
                }

                if ($line->product && (int) $line->product->quantite < $sellQty) {
                    throw new \RuntimeException("Stock insuffisant pour {$line->designation}");
                }

                $lineTotal = round($sellQty * (float) $line->prix_unitaire, 2);
                $linesToSell->push([
                    'line' => $line,
                    'qty' => $sellQty,
                    'line_total' => $lineTotal,
                ]);
            }

            if ($linesToSell->isEmpty()) {
                throw new \RuntimeException('Aucune quantite selectionnee a convertir.');
            }

            $total = (float) $linesToSell->sum('line_total');
            $remise = (float) ($request->remise ?? 0);
            $net = max($total - $remise, 0);
            $modePaiement = $request->mode_paiement;
            $montantPaye = $modePaiement === 'Credit' ? (float) ($request->montant_paye ?? 0) : $net;

            $vente = Vente::create([
                'numero_ticket' => $this->generateTicketNumber(),
                'total' => $total,
                'remise' => $remise,
                'net_a_payer' => $net,
                'montant_paye' => $montantPaye,
                'rendu' => 0,
                'mode_paiement' => $modePaiement,
                'client' => $bon->client_id,
                'user_id' => auth()->id(),
            ]);

            foreach ($linesToSell as $item) {
                $line = $item['line'];
                $qty = (int) $item['qty'];
                $lineTotal = (float) $item['line_total'];

                if ($line->product) {
                    $beforeProduct = (int) $line->product->quantite;
                    $line->product->decrement('quantite', $qty);
                    $afterProduct = (int) $line->product->fresh()->quantite;
                    StockMovement::create([
                        'product_id' => $line->product->id,
                        'variant_id' => null,
                        'movement_type' => 'sale_out_bl',
                        'quantity_delta' => $afterProduct - $beforeProduct,
                        'stock_before' => $beforeProduct,
                        'stock_after' => $afterProduct,
                        'source_type' => 'vente',
                        'source_id' => $vente->id,
                        'notes' => 'Conversion BL en vente',
                        'user_id' => auth()->id(),
                    ]);
                }

                $reference = $line->product ? ($line->product->codebar ?: $line->product->id) : ('BLD-' . $line->id);

                VenteDetail::create([
                    'vente_id' => $vente->id,
                    'reference_produit' => (string) $reference,
                    'variant_id' => null,
                    'nom_produit' => $line->designation,
                    'quantite' => $qty,
                    'prix_unitaire' => $line->prix_unitaire,
                    'total_ligne' => $lineTotal,
                ]);

                $line->quantite_vendue = (int) $line->quantite_vendue + $qty;
                if ((int) $line->quantite_vendue >= (int) $line->quantite) {
                    $line->vente_id = $vente->id;
                }
                $line->save();
            }

            $facture = $this->createInvoiceFromSale($bon, $vente, $linesToSell, (float) ($request->tva_rate ?? 20));
            $this->refreshBonStatus($bon);

            DB::commit();
            return redirect()->route('factures.print', $facture)->with('success', 'BL converti en vente + facture avec succes.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Conversion impossible: ' . $e->getMessage());
        }
    }

    private function syncDetails(BonLivraison $bon, Request $request): float
    {
        $bon->details()->delete();

        $designations = $request->input('designation', []);
        $productIds = $request->input('product_id', []);
        $images = $request->input('image', []);
        $quantites = $request->input('quantite', []);
        $prixUnitaires = $request->input('prix_unitaire', []);

        $total = 0.0;

        $max = max(count($designations), count($quantites), count($prixUnitaires));
        for ($i = 0; $i < $max; $i++) {
            $designation = trim((string) ($designations[$i] ?? ''));
            $qty = (int) ($quantites[$i] ?? 0);
            $prix = (float) ($prixUnitaires[$i] ?? 0);
            $image = trim((string) ($images[$i] ?? ''));
            $productId = !empty($productIds[$i]) ? (int) $productIds[$i] : null;

            if ($designation === '' || $qty <= 0) {
                continue;
            }

            $lineTotal = round($qty * $prix, 2);

            $bon->details()->create([
                'product_id' => $productId,
                'image' => $image !== '' ? $image : null,
                'designation' => $designation,
                'quantite' => $qty,
                'quantite_vendue' => 0,
                'prix_unitaire' => $prix,
                'total_ligne' => $lineTotal,
            ]);

            $total += $lineTotal;
        }

        return round($total, 2);
    }

    private function generateNumero(): string
    {
        $today = now()->format('Ymd');
        $last = BonLivraison::whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($last && preg_match('/BL-\d{8}-(\d+)/', $last->numero, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return 'BL-' . $today . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    private function generateTicketNumber(): string
    {
        $now = now();
        $prefix = $now->format('md');

        $lastVente = Vente::where('numero_ticket', 'like', $prefix . '%')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->orderByDesc('numero_ticket')
            ->first();

        $newCounter = 1;
        if ($lastVente) {
            $lastCounter = intval(substr($lastVente->numero_ticket, -3));
            $newCounter = $lastCounter + 1;
        }

        return $prefix . str_pad((string) $newCounter, 3, '0', STR_PAD_LEFT);
    }

    private function refreshBonStatus(BonLivraison $bon): void
    {
        $bon->load('details');
        $totalQty = (int) $bon->details->sum('quantite');
        $soldQty = (int) $bon->details->sum('quantite_vendue');

        $status = 'brouillon';
        if ($soldQty > 0 && $soldQty < $totalQty) {
            $status = 'partiel';
        } elseif ($totalQty > 0 && $soldQty >= $totalQty) {
            $status = 'livre';
        }

        $bon->status = $status;
        $bon->save();
    }

    private function createInvoiceFromSale(BonLivraison $bon, Vente $vente, $linesToSell, float $tvaRate): Facture
    {
        $totalHt = round((float) $linesToSell->sum('line_total'), 2);
        $tvaAmount = round($totalHt * ($tvaRate / 100), 2);
        $totalTtc = round($totalHt + $tvaAmount, 2);

        $facture = Facture::create([
            'numero' => $this->generateFactureNumber(),
            'date_facture' => now()->toDateString(),
            'client_id' => $bon->client_id,
            'bon_livraison_id' => $bon->id,
            'vente_id' => $vente->id,
            'total_ht' => $totalHt,
            'tva_rate' => $tvaRate,
            'tva_amount' => $tvaAmount,
            'total_ttc' => $totalTtc,
            'legal_company_name' => env('LEGAL_COMPANY_NAME', env('COMPANY_NAME', config('app.name'))),
            'legal_ice' => env('LEGAL_ICE'),
            'legal_rc' => env('LEGAL_RC'),
            'legal_if' => env('LEGAL_IF'),
            'legal_cnss' => env('LEGAL_CNSS'),
            'legal_address' => env('COMPANY_ADDRESS'),
            'legal_phone' => env('COMPANY_PHONE'),
            'legal_email' => env('COMPANY_EMAIL'),
            'user_id' => auth()->id(),
        ]);

        foreach ($linesToSell as $item) {
            $line = $item['line'];
            $facture->details()->create([
                'product_id' => $line->product_id,
                'designation' => $line->designation,
                'quantite' => (int) $item['qty'],
                'prix_unitaire' => $line->prix_unitaire,
                'total_ligne' => (float) $item['line_total'],
            ]);
        }

        return $facture;
    }

    private function generateFactureNumber(): string
    {
        $today = now()->format('Ymd');
        $last = Facture::whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($last && preg_match('/FAC-\d{8}-(\d+)/', $last->numero, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return 'FAC-' . $today . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}
