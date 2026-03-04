<?php

namespace App\Http\Controllers;

use App\Avoir;
use App\Product;
use App\ProductVariant;
use App\StockMovement;
use App\Vente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AvoirController extends Controller
{
    public function index(Request $request)
    {
        $query = Avoir::with(['client', 'vente'])->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%$search%")
                    ->orWhereHas('client', function ($qc) use ($search) {
                        $qc->where('nom', 'like', "%$search%")
                            ->orWhere('telephone', 'like', "%$search%");
                    })
                    ->orWhereHas('vente', function ($qv) use ($search) {
                        $qv->where('numero_ticket', 'like', "%$search%");
                    });
            });
        }

        $avoirs = $query->paginate(20)->appends($request->all());
        return view('avoirs.index', compact('avoirs'));
    }

    public function createFromSale(Vente $vente)
    {
        $vente->load(['details', 'clientInfo']);
        return view('avoirs.create_from_sale', compact('vente'));
    }

    public function storeFromSale(Request $request, Vente $vente)
    {
        $request->validate([
            'detail_ids' => 'required|array|min:1',
            'detail_ids.*' => 'required|integer',
            'return_qty' => 'required|array',
            'return_qty.*' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $lines = $vente->details()->whereIn('id', $request->detail_ids)->get();
        if ($lines->isEmpty()) {
            return back()->with('error', 'Aucune ligne valide selectionnee.');
        }

        DB::beginTransaction();
        try {
            $avoir = Avoir::create([
                'numero' => $this->generateNumero(),
                'date_avoir' => now()->toDateString(),
                'client_id' => is_numeric($vente->client) ? (int) $vente->client : null,
                'vente_id' => $vente->id,
                'total' => 0,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            $total = 0.0;

            foreach ($lines as $line) {
                $qtyToReturn = (int) ($request->return_qty[$line->id] ?? 0);
                $soldQty = max((int) $line->quantite, 0);
                $alreadyReturned = max((int) ($line->quantite_retournee ?? 0), 0);
                $remaining = max($soldQty - $alreadyReturned, 0);

                if ($qtyToReturn <= 0) {
                    continue;
                }

                if ($qtyToReturn > $remaining) {
                    throw new \RuntimeException("Quantite retour invalide pour {$line->nom_produit}. Reste possible: {$remaining}");
                }

                $lineTotal = round($qtyToReturn * (float) $line->prix_unitaire, 2);

                $product = $this->findProductByReference((string) $line->reference_produit);
                if ($product) {
                    $beforeProduct = (int) $product->quantite;
                    $product->increment('quantite', $qtyToReturn);
                    $afterProduct = (int) $product->fresh()->quantite;
                    StockMovement::create([
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'movement_type' => 'customer_return_avoir',
                        'quantity_delta' => $afterProduct - $beforeProduct,
                        'stock_before' => $beforeProduct,
                        'stock_after' => $afterProduct,
                        'source_type' => 'avoir',
                        'source_id' => $avoir->id,
                        'notes' => 'Retour client via avoir',
                        'user_id' => auth()->id(),
                    ]);

                    if (!empty($line->variant_id)) {
                        $variant = ProductVariant::where('product_id', $product->id)->find($line->variant_id);
                        if ($variant) {
                            $beforeVariant = (int) $variant->quantity;
                            $variant->increment('quantity', $qtyToReturn);
                            $afterVariant = (int) $variant->fresh()->quantity;
                            StockMovement::create([
                                'product_id' => $product->id,
                                'variant_id' => $variant->id,
                                'movement_type' => 'customer_return_avoir',
                                'quantity_delta' => $afterVariant - $beforeVariant,
                                'stock_before' => $beforeVariant,
                                'stock_after' => $afterVariant,
                                'source_type' => 'avoir',
                                'source_id' => $avoir->id,
                                'notes' => 'Retour client via avoir',
                                'user_id' => auth()->id(),
                            ]);
                        }
                    }
                }

                $avoir->details()->create([
                    'vente_detail_id' => $line->id,
                    'product_id' => $product ? $product->id : null,
                    'variant_id' => $line->variant_id,
                    'reference_produit' => $line->reference_produit,
                    'nom_produit' => $line->nom_produit,
                    'quantite' => $qtyToReturn,
                    'prix_unitaire' => $line->prix_unitaire,
                    'total_ligne' => $lineTotal,
                ]);

                $line->quantite_retournee = $alreadyReturned + $qtyToReturn;
                $line->save();

                $total += $lineTotal;
            }

            if ($total <= 0) {
                throw new \RuntimeException('Aucune quantite retour valide.');
            }

            $avoir->total = round($total, 2);
            $avoir->save();

            DB::commit();
            return redirect()->route('avoirs.print', $avoir)->with('success', 'Avoir cree avec succes.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Creation avoir impossible: ' . $e->getMessage());
        }
    }

    public function print(Avoir $avoir)
    {
        $avoir->load(['details', 'client', 'vente']);
        return view('avoirs.print', compact('avoir'));
    }

    private function generateNumero(): string
    {
        $today = now()->format('Ymd');
        $last = Avoir::whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($last && preg_match('/AVR-\d{8}-(\d+)/', $last->numero, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return 'AVR-' . $today . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    private function findProductByReference(string $reference): ?Product
    {
        $reference = trim($reference);
        if ($reference === '') {
            return null;
        }

        $product = Product::where('codebar', $reference)->first();
        if ($product) {
            return $product;
        }

        if (ctype_digit($reference)) {
            return Product::find((int) $reference);
        }

        return null;
    }
}
