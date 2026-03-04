<?php

namespace App\Http\Controllers;

use App\Category;
use App\Client;
use App\Product;
use App\ProductVariant;
use App\StockMovement;
use App\Vente;
use App\VenteDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VenteController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->orderByDesc('created_at')
            ->take(200)
            ->get();
        $categories = Category::orderBy('name')->get();
        $panier = session('panier', []);

        return view('caisse.index', compact('products', 'categories', 'panier'));
    }

    public function editvente($id)
    {
        $vente = Vente::with(['details', 'clientInfo'])->findOrFail($id);
        $produits = Product::all();
        return view('ventes.edit', compact('vente', 'produits'));
    }

    public function updatevente(Request $request, $id)
    {
        $request->validate([
            'mode_paiement' => 'required|string',
            'remise' => 'nullable|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
        ]);

        $vente = Vente::findOrFail($id);

        $vente->update([
            'mode_paiement' => $request->mode_paiement,
            'remise' => $request->remise ?? 0,
            'montant_paye' => $request->montant_paye,
            'net_a_payer' => $vente->total - ($request->remise ?? 0),
        ]);

        return redirect()->back()->with('success', 'Vente modifiee avec succes');
    }

    public function addToCart(Request $request)
    {
        $code = trim((string) $request->input('code'));
        $productId = $request->input('product_id');
        $variantId = $request->input('variant_id');
        $cart = session()->get('panier', []);
        $key = '';
        $product = null;
        $selectedVariant = null;

        if ($code !== '') {
            $product = Product::where('codebar', $code)->first();
            if ($product) {
                $key = 'codebar-' . $code;
            }
        }

        if (!$product && !empty($productId)) {
            $product = Product::find($productId);
            if ($product) {
                $key = 'product-' . $product->id;
            }
        }

        if (!$product) {
            return response()->json(['error' => 'Produit introuvable'], 404);
        }

        $product->loadMissing('variants.color');

        if ($product->has_variants) {
            if (!empty($variantId)) {
                $selectedVariant = $product->variants->firstWhere('id', (int) $variantId);
                if (!$selectedVariant) {
                    return response()->json(['error' => 'Variante introuvable pour ce produit'], 422);
                }
            } elseif ($product->variants->count() > 1) {
                return response()->json([
                    'needs_variant' => true,
                    'product' => [
                        'id' => $product->id,
                        'name' => trim($product->marque . ' ' . $product->modele),
                        'codebar' => $product->codebar,
                        'reference' => $product->reference,
                    ],
                    'variants' => $product->variants->map(function ($variant) {
                        return [
                            'id' => $variant->id,
                            'size' => $variant->size,
                            'color' => optional($variant->color)->name,
                            'color_hex' => optional($variant->color)->hex_code,
                            'quantity' => (int) $variant->quantity,
                            'label' => $this->variantLabel($variant),
                        ];
                    })->values(),
                ], 409);
            } elseif ($product->variants->count() === 1) {
                $selectedVariant = $product->variants->first();
            }
        }

        $availableQty = $selectedVariant ? (int) $selectedVariant->quantity : (int) $product->quantite;
        if ($selectedVariant) {
            $key = ($key !== '' ? $key : 'product-' . $product->id) . '-v' . $selectedVariant->id;
        }

        $currentQty = isset($cart[$key]) ? (int) ($cart[$key]['quantite'] ?? 0) : 0;
        if ($availableQty <= 0 || $currentQty >= $availableQty) {
            return response()->json(['error' => 'Stock insuffisant pour cette variante'], 422);
        }

        if (isset($cart[$key])) {
            $cart[$key]['quantite']++;
        } else {
            $cart[$key] = [
                'id' => $product->codebar ?: $product->id,
                'product_id' => $product->id,
                'nom' => trim($product->marque . ' ' . $product->modele),
                'reference' => $product->reference,
                'prix' => $product->prix_vente,
                'quantite' => 1,
                'codebar' => $product->codebar,
                'variant_id' => $selectedVariant ? $selectedVariant->id : null,
                'variant_label' => $selectedVariant ? $this->variantLabel($selectedVariant) : null,
            ];
        }

        session()->put('panier', $cart);

        return response()->json(array_merge([
            'success' => 'Ajoute au panier',
            'added_key' => $key,
            'added_name' => $cart[$key]['nom'] ?? 'Produit',
        ], $this->buildCartPayload($cart)));
    }

    public function validerVente(Request $request)
    {
        Log::info('Debut validation vente', $request->all());

        $request->validate([
            'mode_paiement' => 'required|string',
            'total' => 'required|numeric',
        ]);

        $isComptoir = $request->comptoir == 1;

        $panier = session('panier', []);
        if (empty($panier)) {
            Log::warning('Panier vide a la validation');
            return back()->with('error', 'Le panier est vide.');
        }

        $total = floatval($request->input('total', 0));
        $typeRemise = $request->input('type_remise', 'dh');
        $remiseValeur = floatval($request->input('remise', 0));
        $remise = $typeRemise === '%' ? $total * ($remiseValeur / 100) : $remiseValeur;
        $net = max($total - $remise, 0);
        $rendu = 0;

        if ($isComptoir) {
            $client = null;
        } else {
            $client = Client::firstOrCreate(
                ['telephone' => $request->new_client_telephone],
                ['nom' => $request->new_client_nom]
            );
        }

        DB::beginTransaction();
        try {
            $now = now();
            $prefix = $now->format('md');

            $lastVente = Vente::where('numero_ticket', 'like', $prefix . '%')
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->orderByDesc('numero_ticket')
                ->first();

            $newCompteur = $lastVente ? intval(substr($lastVente->numero_ticket, -3)) + 1 : 1;
            $numeroTicket = $prefix . str_pad($newCompteur, 3, '0', STR_PAD_LEFT);

            $montantPaye = $request->mode_paiement === 'Credit'
                ? floatval($request->montant_paye)
                : floatval($net);

            $vente = Vente::create([
                'numero_ticket' => $numeroTicket,
                'total' => $total,
                'remise' => $remise,
                'net_a_payer' => $net,
                'montant_paye' => $montantPaye,
                'rendu' => $rendu,
                'mode_paiement' => $request->mode_paiement,
                'client' => $client ? $client->id : null,
                'user_id' => auth()->id(),
            ]);

            foreach ($panier as $item) {
                $reference = $item['id'];
                $variantId = $item['variant_id'] ?? null;
                $quantite = ($item['retour'] ?? false) ? -$item['quantite'] : $item['quantite'];
                $totalLigne = $item['prix'] * $quantite;

                VenteDetail::create([
                    'vente_id' => $vente->id,
                    'reference_produit' => $reference,
                    'variant_id' => $variantId,
                    'nom_produit' => $item['nom'],
                    'quantite' => $quantite,
                    'prix_unitaire' => $item['prix'],
                    'total_ligne' => $totalLigne,
                ]);

                $product = null;
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                }
                if (!$product) {
                    $product = $this->findProductByReference((string) $reference);
                }

                if ($product) {
                    if (!empty($variantId)) {
                        $variant = ProductVariant::where('product_id', $product->id)->find($variantId);
                        if ($variant) {
                            $beforeVariant = (int) $variant->quantity;
                            if ($quantite > 0) {
                                $variant->decrement('quantity', $quantite);
                            } else {
                                $variant->increment('quantity', abs($quantite));
                            }
                            $afterVariant = (int) $variant->fresh()->quantity;
                            StockMovement::create([
                                'product_id' => $product->id,
                                'variant_id' => $variant->id,
                                'movement_type' => $quantite > 0 ? 'sale_out' : 'sale_return_in',
                                'quantity_delta' => $afterVariant - $beforeVariant,
                                'stock_before' => $beforeVariant,
                                'stock_after' => $afterVariant,
                                'source_type' => 'vente',
                                'source_id' => $vente->id,
                                'notes' => 'Caisse vente',
                                'user_id' => auth()->id(),
                            ]);
                        }
                    }

                    $beforeProduct = (int) $product->quantite;
                    if ($quantite > 0) {
                        $product->decrement('quantite', $quantite);
                    } else {
                        $product->increment('quantite', abs($quantite));
                    }
                    $afterProduct = (int) $product->fresh()->quantite;
                    StockMovement::create([
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'movement_type' => $quantite > 0 ? 'sale_out' : 'sale_return_in',
                        'quantity_delta' => $afterProduct - $beforeProduct,
                        'stock_before' => $beforeProduct,
                        'stock_after' => $afterProduct,
                        'source_type' => 'vente',
                        'source_id' => $vente->id,
                        'notes' => 'Caisse vente',
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            session()->forget('panier');
            DB::commit();
            Log::info('Vente enregistree : ID ' . $vente->id);

            return redirect()->route('caisse.index')->with('success', 'Vente enregistree avec succes.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur validation vente : ' . $e->getMessage());

            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function vider(Request $request)
    {
        session()->forget('panier');
        $cart = [];

        if ($request->expectsJson()) {
            return response()->json(array_merge([
                'success' => 'Panier vide.',
            ], $this->buildCartPayload($cart)));
        }

        return back()->with('success', 'Panier vide.');
    }

    public function remove(Request $request, $id)
    {
        $panier = session('panier', []);
        unset($panier[$id]);
        session(['panier' => $panier]);

        if ($request->expectsJson()) {
            return response()->json(array_merge([
                'success' => 'Produit supprime du panier.',
            ], $this->buildCartPayload($panier)));
        }

        return back()->with('success', 'Produit supprime du panier.');
    }

    public function historique(Request $request)
    {
        $query = Vente::with(['details', 'clientInfo']);

        if ($request->filled('client')) {
            $query->whereHas('clientInfo', function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->client . '%')
                    ->orWhere('telephone', 'like', '%' . $request->client . '%');
            });
        }

        if ($request->filled('fournisseur')) {
            $fournisseur = $request->fournisseur;
            $query->whereHas('details', function ($qd) use ($fournisseur) {
                $qd->where(function ($inner) use ($fournisseur) {
                    $inner->whereIn('reference_produit', function ($sub) use ($fournisseur) {
                        $sub->select('products.codebar')
                            ->from('products')
                            ->join('fournisseurs', 'products.fournisseur_id', '=', 'fournisseurs.id')
                            ->where('fournisseurs.nom', 'like', '%' . $fournisseur . '%');
                    })->orWhereIn('reference_produit', function ($sub) use ($fournisseur) {
                        $sub->select(DB::raw('CAST(products.id AS CHAR)'))
                            ->from('products')
                            ->join('fournisseurs', 'products.fournisseur_id', '=', 'fournisseurs.id')
                            ->where('fournisseurs.nom', 'like', '%' . $fournisseur . '%');
                    });
                });
            });
        }

        if ($request->filled('produit')) {
            $produit = $request->produit;

            $query->whereHas('details', function ($qd) use ($produit) {
                $qd->where('nom_produit', 'like', '%' . $produit . '%')
                    ->orWhere('reference_produit', 'like', '%' . $produit . '%');
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('paiement')) {
            if ($request->paiement === 'Credit') {
                $query->where('mode_paiement', 'Credit')
                    ->whereColumn('net_a_payer', '>', 'montant_paye');
            } else {
                $query->where('mode_paiement', $request->paiement);
            }
        }

        $totalNet = $query->sum('net_a_payer');
        $ventes = $query->latest()->paginate(15)->appends($request->all());

        return view('ventes.historique', compact('ventes', 'totalNet'));
    }

    public function toggleRetour(Request $request, $key)
    {
        $panier = session('panier', []);

        if (isset($panier[$key])) {
            $panier[$key]['retour'] = !($panier[$key]['retour'] ?? false);
            session(['panier' => $panier]);
        }

        if ($request->expectsJson()) {
            return response()->json(array_merge([
                'success' => 'Quantite retour mise a jour.',
            ], $this->buildCartPayload($panier)));
        }

        return back();
    }

    private function buildCartPayload(array $cart): array
    {
        $total = 0.0;
        $itemsCount = 0;

        foreach ($cart as $item) {
            $qty = (int) ($item['quantite'] ?? 0);
            $signedQty = ($item['retour'] ?? false) ? -$qty : $qty;
            $price = (float) ($item['prix'] ?? 0);
            $total += $price * $signedQty;
            $itemsCount += abs($qty);
        }

        return [
            'panier' => $cart,
            'cart_total' => round($total, 2),
            'cart_items_count' => $itemsCount,
        ];
    }

    private function variantLabel(ProductVariant $variant): string
    {
        $parts = [];
        if (!empty($variant->size)) {
            $parts[] = 'Taille ' . $variant->size;
        }
        if (!empty(optional($variant->color)->name)) {
            $parts[] = 'Couleur ' . optional($variant->color)->name;
        }

        return count($parts) ? implode(' / ', $parts) : 'Variante';
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $vente = Vente::with('details')->findOrFail($id);

            foreach ($vente->details as $detail) {
                $product = $this->findProductByReference((string) $detail->reference_produit);

                if ($product) {
                    $qtyToRestore = abs($detail->quantite);
                    $beforeProduct = (int) $product->quantite;
                    $product->increment('quantite', $qtyToRestore);
                    $afterProduct = (int) $product->fresh()->quantite;
                    StockMovement::create([
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'movement_type' => 'sale_delete_restore',
                        'quantity_delta' => $afterProduct - $beforeProduct,
                        'stock_before' => $beforeProduct,
                        'stock_after' => $afterProduct,
                        'source_type' => 'vente',
                        'source_id' => $vente->id,
                        'notes' => 'Suppression vente',
                        'user_id' => auth()->id(),
                    ]);

                    if (!empty($detail->variant_id)) {
                        $variant = ProductVariant::where('product_id', $product->id)->find($detail->variant_id);
                        if ($variant) {
                            $beforeVariant = (int) $variant->quantity;
                            $variant->increment('quantity', $qtyToRestore);
                            $afterVariant = (int) $variant->fresh()->quantity;
                            StockMovement::create([
                                'product_id' => $product->id,
                                'variant_id' => $variant->id,
                                'movement_type' => 'sale_delete_restore',
                                'quantity_delta' => $afterVariant - $beforeVariant,
                                'stock_before' => $beforeVariant,
                                'stock_after' => $afterVariant,
                                'source_type' => 'vente',
                                'source_id' => $vente->id,
                                'notes' => 'Suppression vente',
                                'user_id' => auth()->id(),
                            ]);
                        }
                    }
                }
            }

            $vente->details()->delete();
            $vente->delete();

            DB::commit();

            return back()->with('success', 'Vente supprimee et stock restaure avec succes.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur suppression vente : ' . $e->getMessage());
        }
    }

    public function imprimerTicket()
    {
        $panier = session('panier', []);
        $total = 0;

        foreach ($panier as $item) {
            $total += $item['prix'] * $item['quantite'];
        }

        $client = session('client', [
            'nom' => null,
            'telephone' => null,
        ]);

        $mode_paiement = session('mode_paiement', null);

        return view('caisse.ticket', compact('panier', 'total', 'client', 'mode_paiement'));
    }

    public function storeInfosTicket(Request $request)
    {
        $nom = trim((string) $request->input('nom', ''));
        $telephone = trim((string) $request->input('telephone', ''));

        session([
            'client' => [
                'nom' => $nom !== '' ? $nom : null,
                'telephone' => $telephone !== '' ? $telephone : null,
                'remise' => $request->input('remise'),
                'type_remise' => $request->input('type_remise'),
                'net_a_payer' => $request->input('net_a_payer'),
            ],
            'mode_paiement' => $request->input('mode'),
        ]);

        return response()->json(['success' => true]);
    }

    public function dashboard(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect('/login');
        }

        $start = $request->input('start');
        $end = $request->input('end');

        $ventes = Vente::with('details')
            ->when($start, function ($q) use ($start) {
                return $q->whereDate('created_at', '>=', $start);
            })
            ->when($end, function ($q) use ($end) {
                return $q->whereDate('created_at', '<=', $end);
            })
            ->get();

        $ca_brut = 0;
        $cout_total = 0;
        $produits = [];

        foreach ($ventes as $vente) {
            foreach ($vente->details as $detail) {
                $produit = $this->findProductByReference((string) $detail->reference_produit);
                $prix_achat = $produit ? $produit->prix_achat : 0;

                $ca_ligne = $detail->prix_unitaire * $detail->quantite;
                $cout_ligne = $prix_achat * $detail->quantite;

                $ca_brut += $ca_ligne;
                $cout_total += $cout_ligne;

                $key = $detail->reference_produit;

                if (!isset($produits[$key])) {
                    $produits[$key] = [
                        'designation' => $detail->nom_produit,
                        'quantite' => 0,
                        'ca' => 0,
                    ];
                }

                $produits[$key]['quantite'] += $detail->quantite;
                $produits[$key]['ca'] += $ca_ligne;
            }
        }

        $total_remise = $ventes->sum('remise');

        $ca_net = $ca_brut - $total_remise;
        $marge = $ca_net - $cout_total;

        $topProduits = collect($produits)
            ->sortByDesc('quantite')
            ->take(20);

        $valeur_stock = Product::sum(DB::raw('prix_achat * quantite'));
        $total_articles = Product::sum('quantite');

        $valeur_stock_par_categorie = Product::select(
            'categorie',
            DB::raw('SUM(prix_achat * quantite) as total')
        )
            ->groupBy('categorie')
            ->orderBy('categorie')
            ->get();

        return view('dashboard', compact(
            'ventes',
            'ca_net',
            'marge',
            'topProduits',
            'start',
            'end',
            'valeur_stock',
            'total_articles',
            'valeur_stock_par_categorie'
        ));
    }

    public function payerCredit(Request $request, Vente $vente)
    {
        $montant = floatval($request->input('montant'));

        $reste = $vente->net_a_payer - $vente->montant_paye;

        if ($montant <= 0 || $montant > $reste) {
            return back()->with('error', 'Montant invalide.');
        }

        $vente->montant_paye += $montant;
        $vente->save();

        return back()->with('success', 'Credit mis a jour avec succes.');
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
