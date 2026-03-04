<?php

namespace App\Http\Controllers;

use App\Client;
use App\Devis;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevisController extends Controller
{
    public function index(Request $request)
    {
        $query = Devis::with('client')->latest();

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

        $devis = $query->paginate(20)->appends($request->all());
        return view('devis.index', compact('devis'));
    }

    public function create()
    {
        $clients = Client::orderBy('nom')->get();
        $products = Product::orderBy('marque')->get();
        return view('devis.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_devis' => 'required|date',
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
            $devis = Devis::create([
                'numero' => $this->generateNumero(),
                'date_devis' => $request->date_devis,
                'client_id' => $request->client_id,
                'notes' => $request->notes,
                'total' => 0,
                'status' => 'brouillon',
                'user_id' => auth()->id(),
            ]);

            $total = $this->syncDetails($devis, $request);
            $devis->update(['total' => $total]);

            DB::commit();
            return redirect()->route('devis.index')->with('success', 'Devis cree avec succes.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur creation devis: ' . $e->getMessage());
        }
    }

    public function edit(Devis $devi)
    {
        $devis = $devi->load('details');
        $clients = Client::orderBy('nom')->get();
        $products = Product::orderBy('marque')->get();

        return view('devis.edit', compact('devis', 'clients', 'products'));
    }

    public function update(Request $request, Devis $devi)
    {
        $request->validate([
            'date_devis' => 'required|date',
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
            $devi->update([
                'date_devis' => $request->date_devis,
                'client_id' => $request->client_id,
                'notes' => $request->notes,
            ]);

            $total = $this->syncDetails($devi, $request);
            $devi->update(['total' => $total]);

            DB::commit();
            return redirect()->route('devis.index')->with('success', 'Devis modifie avec succes.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur modification devis: ' . $e->getMessage());
        }
    }

    public function destroy(Devis $devi)
    {
        $devi->delete();
        return back()->with('success', 'Devis supprime avec succes.');
    }

    public function print(Devis $devi)
    {
        $devis = $devi->load('details', 'client');
        return view('devis.print', compact('devis'));
    }

    private function syncDetails(Devis $devis, Request $request): float
    {
        $devis->details()->delete();

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

            $devis->details()->create([
                'product_id' => $productId,
                'image' => $image !== '' ? $image : null,
                'designation' => $designation,
                'quantite' => $qty,
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
        $last = Devis::whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($last && preg_match('/DEV-\d{8}-(\d+)/', $last->numero, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return 'DEV-' . $today . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}