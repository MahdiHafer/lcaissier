<?php

namespace App\Http\Controllers;

use App\Category;
use App\Color;
use App\Fournisseur;
use App\Product;
use App\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function printLabel(Product $product, Request $request)
    {
        $rawCode = $product->codebar;
        $fallbackCode = 'PROD-' . str_pad((string) $product->id, 6, '0', STR_PAD_LEFT);
        [$barcodeSrc, $barcodeCode] = $this->buildBarcodeImage($rawCode, $fallbackCode);

        return view('products.print-label', [
            'product' => $product,
            'barcodeSrc' => $barcodeSrc,
            'barcodeCode' => $barcodeCode,
        ]);
    }

    public function index(Request $request)
    {
        $query = Product::with('fournisseur', 'category', 'variants.color');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('codebar', 'like', "%$search%")
                    ->orWhere('reference', 'like', "%$search%")
                    ->orWhere('marque', 'like', "%$search%");
            });
        }

        if ($request->filled('fournisseur_id')) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }

        if ($request->filled('etat')) {
            $query->where('etat', $request->etat);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        } elseif ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        if ($request->filled('type') && $request->type === 'telephone') {
            $query->where('categorie', '!=', 'Produit');
        }

        if ($request->filled('rupture')) {
            $query->where('quantite', '<=', 0);
        }

        $products = $query->orderByDesc('created_at')->get();

        return view('products.index', [
            'products' => $products,
            'type' => $request->type,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $fournisseurs = Fournisseur::orderBy('nom')->get();
        $categories = Category::orderBy('name')->get();
        $colors = Color::orderBy('name')->get();

        return view('products.create', compact('fournisseurs', 'categories', 'colors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'nullable|string|regex:/^[A-Z0-9]{3}\d{2}\d{3}[A-Z0-9]{2,}$/|unique:products,reference',
            'reference_type' => 'nullable|string|size:3|regex:/^[A-Za-z0-9]{3}$/',
            'reference_group' => 'nullable|string|size:2|regex:/^\d{2}$/',
            'reference_line' => 'nullable|string|min:1|max:20|regex:/^[A-Za-z0-9]+$/',
            'reference_season' => 'nullable|string|size:1|regex:/^[A-Za-z0-9]$/',
            'codebar' => 'required|string|unique:products,codebar',
            'marque' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'categorie' => 'nullable|string|max:255',
            'etat' => 'required|in:Neuf,Occasion',
            'tva_rate' => 'nullable|numeric|min:0|max:100',
            'prix_achat_ht' => 'nullable|numeric|min:0',
            'prix_achat_ttc' => 'nullable|numeric|min:0',
            'prix_vente_ht' => 'nullable|numeric|min:0',
            'prix_vente_ttc' => 'nullable|numeric|min:0',
            'quantite' => 'nullable|integer|min:0',
            'has_variants' => 'nullable|boolean',
            'fournisseur_id' => 'nullable|exists:fournisseurs,id',
            'image' => 'nullable|image|max:2048',
            'variant_size' => 'nullable|array',
            'variant_size.*' => 'nullable|string|max:40',
            'variant_color_id' => 'nullable|array',
            'variant_color_id.*' => 'nullable|exists:colors,id',
            'variant_quantity' => 'nullable|array',
            'variant_quantity.*' => 'nullable|integer|min:0',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $filename = uniqid('product_') . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/products'), $filename);
            $imagePath = 'uploads/products/' . $filename;
        }

        $category = null;
        if (!empty($validated['category_id'])) {
            $category = Category::find($validated['category_id']);
        }

        $reference = $this->resolveReference($request);

        [$prixAchatHt, $prixAchatTtc] = $this->normalizeHtTtc($validated['prix_achat_ht'] ?? null, $validated['prix_achat_ttc'] ?? null, (float) ($validated['tva_rate'] ?? 0), 'achat');
        [$prixVenteHt, $prixVenteTtc] = $this->normalizeHtTtc($validated['prix_vente_ht'] ?? null, $validated['prix_vente_ttc'] ?? null, (float) ($validated['tva_rate'] ?? 0), 'vente');

        $product = Product::create([
            'category_id' => $validated['category_id'] ?? null,
            'reference' => $reference,
            'codebar' => $validated['codebar'],
            'marque' => $validated['marque'],
            'categorie' => $category ? $category->name : ($validated['categorie'] ?? 'Produit'),
            'etat' => $validated['etat'],
            'tva_rate' => (float) ($validated['tva_rate'] ?? 0),
            'prix_achat_ht' => $prixAchatHt,
            'prix_achat_ttc' => $prixAchatTtc,
            'prix_vente_ht' => $prixVenteHt,
            'prix_vente_ttc' => $prixVenteTtc,
            'prix_achat' => $prixAchatTtc,
            'prix_vente' => $prixVenteTtc,
            'quantite' => $validated['quantite'] ?? 0,
            'has_variants' => (bool) $request->boolean('has_variants'),
            'modele' => '',
            'ram' => null,
            'stockage' => null,
            'processeur' => null,
            'fournisseur_id' => $validated['fournisseur_id'] ?? null,
            'image' => $imagePath,
        ]);

        if ($product->has_variants) {
            $variantTotal = $this->syncVariants($product, $request);
            $product->update(['quantite' => $variantTotal]);
        } else {
            $product->variants()->delete();
        }

        return redirect()->route('products.index', ['type' => 'produit'])
            ->with('success', 'Produit ajoute avec succes.');
    }

    public function edit(Product $product)
    {
        $fournisseurs = Fournisseur::orderBy('nom')->get();
        $categories = Category::orderBy('name')->get();
        $colors = Color::orderBy('name')->get();
        $product->load('variants');

        return view('products.edit', compact('product', 'fournisseurs', 'categories', 'colors'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'reference' => 'nullable|string|regex:/^[A-Z0-9]{3}\d{2}\d{3}[A-Z0-9]{2,}$/|unique:products,reference,' . $product->id,
            'reference_type' => 'nullable|string|size:3|regex:/^[A-Za-z0-9]{3}$/',
            'reference_group' => 'nullable|string|size:2|regex:/^\d{2}$/',
            'reference_line' => 'nullable|string|min:1|max:20|regex:/^[A-Za-z0-9]+$/',
            'reference_season' => 'nullable|string|size:1|regex:/^[A-Za-z0-9]$/',
            'codebar' => 'nullable|string|unique:products,codebar,' . $product->id,
            'marque' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'categorie' => 'nullable|string|max:255',
            'etat' => 'required|in:Neuf,Occasion',
            'tva_rate' => 'nullable|numeric|min:0|max:100',
            'prix_achat_ht' => 'nullable|numeric|min:0',
            'prix_achat_ttc' => 'nullable|numeric|min:0',
            'prix_vente_ht' => 'nullable|numeric|min:0',
            'prix_vente_ttc' => 'nullable|numeric|min:0',
            'quantite' => 'nullable|integer|min:0',
            'has_variants' => 'nullable|boolean',
            'fournisseur_id' => 'nullable|exists:fournisseurs,id',
            'image' => 'nullable|image|max:2048',
            'variant_size' => 'nullable|array',
            'variant_size.*' => 'nullable|string|max:40',
            'variant_color_id' => 'nullable|array',
            'variant_color_id.*' => 'nullable|exists:colors,id',
            'variant_quantity' => 'nullable|array',
            'variant_quantity.*' => 'nullable|integer|min:0',
        ]);

        $reference = $this->resolveReference($request, $product->reference);

        [$prixAchatHt, $prixAchatTtc] = $this->normalizeHtTtc($validated['prix_achat_ht'] ?? null, $validated['prix_achat_ttc'] ?? null, (float) ($validated['tva_rate'] ?? 0), 'achat');
        [$prixVenteHt, $prixVenteTtc] = $this->normalizeHtTtc($validated['prix_vente_ht'] ?? null, $validated['prix_vente_ttc'] ?? null, (float) ($validated['tva_rate'] ?? 0), 'vente');

        $data = [
            'reference' => $reference,
            'codebar' => $validated['codebar'] ?? $product->codebar,
            'marque' => $validated['marque'],
            'category_id' => $validated['category_id'] ?? null,
            'categorie' => $validated['categorie'] ?? $product->categorie,
            'etat' => $validated['etat'],
            'tva_rate' => (float) ($validated['tva_rate'] ?? 0),
            'prix_achat_ht' => $prixAchatHt,
            'prix_achat_ttc' => $prixAchatTtc,
            'prix_vente_ht' => $prixVenteHt,
            'prix_vente_ttc' => $prixVenteTtc,
            'prix_achat' => $prixAchatTtc,
            'prix_vente' => $prixVenteTtc,
            'quantite' => $validated['quantite'] ?? $product->quantite,
            'has_variants' => (bool) $request->boolean('has_variants'),
            'fournisseur_id' => $validated['fournisseur_id'] ?? null,
        ];

        if (!empty($validated['category_id'])) {
            $category = Category::find($validated['category_id']);
            if ($category) {
                $data['categorie'] = $category->name;
            }
        }

        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }

            $filename = uniqid('product_') . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/products'), $filename);
            $data['image'] = 'uploads/products/' . $filename;
        }

        $product->update($data);

        if ($product->has_variants) {
            $variantTotal = $this->syncVariants($product, $request);
            $product->update(['quantite' => $variantTotal]);
        } else {
            $product->variants()->delete();
        }

        return redirect()->route('products.index')->with('success', 'Produit mis a jour avec succes.');
    }

    public function destroy(Product $product)
    {
        if ($product->image && file_exists(public_path($product->image))) {
            unlink(public_path($product->image));
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produit supprime avec succes.');
    }

    public function recalculateGlobalStock()
    {
        $updated = 0;
        $products = Product::with('variants')->get();

        DB::beginTransaction();
        try {
            foreach ($products as $product) {
                if (!$product->has_variants) {
                    continue;
                }

                $total = (int) $product->variants->sum('quantity');
                if ((int) $product->quantite !== $total) {
                    $product->quantite = $total;
                    $product->save();
                    $updated++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur recalcul stock: ' . $e->getMessage());
        }

        return back()->with('success', "Recalcul termine. $updated produit(s) corrige(s).");
    }

    public function exportBon()
    {
        return back()->with('error', 'Export bon non disponible dans cette version.');
    }

    public function nextReference(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|size:3|regex:/^[A-Za-z0-9]{3}$/',
            'group' => 'required|string|size:2|regex:/^\d{2}$/',
            'line' => 'required|string|min:1|max:20|regex:/^[A-Za-z0-9]+$/',
            'season' => 'required|string|size:1|regex:/^[A-Za-z0-9]$/',
        ]);

        $type = strtoupper($validated['type']);
        $group = $validated['group'];
        $line = strtoupper($validated['line']);
        $season = strtoupper($validated['season']);
        $next = $this->getNextReferenceSequence();

        return response()->json([
            'reference' => $type . $group . str_pad((string) $next, 3, '0', STR_PAD_LEFT) . $line . $season,
            'sequence' => str_pad((string) $next, 3, '0', STR_PAD_LEFT),
        ]);
    }

    private function syncVariants(Product $product, Request $request)
    {
        $sizes = $request->input('variant_size', []);
        $colorIds = $request->input('variant_color_id', []);
        $quantities = $request->input('variant_quantity', []);

        $product->variants()->delete();

        $total = 0;
        $max = max(count($sizes), count($colorIds), count($quantities));

        for ($i = 0; $i < $max; $i++) {
            $size = trim($sizes[$i] ?? '');
            $colorId = $colorIds[$i] ?? null;
            $quantity = (int) ($quantities[$i] ?? 0);

            if ($size === '' && empty($colorId) && $quantity <= 0) {
                continue;
            }

            ProductVariant::create([
                'product_id' => $product->id,
                'size' => $size !== '' ? $size : null,
                'color_id' => !empty($colorId) ? $colorId : null,
                'quantity' => $quantity,
            ]);

            $total += $quantity;
        }

        return $total;
    }

    private function buildBarcodeImage(?string $rawCode, string $fallbackCode): array
    {
        $normalized = $this->normalizeBarcodeValue($rawCode);
        if ($normalized === '') {
            $normalized = $fallbackCode;
        }

        try {
            $pngGenerator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $image = base64_encode($pngGenerator->getBarcode($normalized, $pngGenerator::TYPE_CODE_128));
            return ['data:image/png;base64,' . $image, $normalized];
        } catch (\Throwable $e) {
            try {
                $svgGenerator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                $svg = $svgGenerator->getBarcode($normalized, $svgGenerator::TYPE_CODE_128);
                return ['data:image/svg+xml;base64,' . base64_encode($svg), $normalized];
            } catch (\Throwable $e2) {
                $safeFallback = $this->normalizeBarcodeValue($fallbackCode);
                $svgGenerator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                $svg = $svgGenerator->getBarcode($safeFallback, $svgGenerator::TYPE_CODE_128);
                return ['data:image/svg+xml;base64,' . base64_encode($svg), $safeFallback];
            }
        }
    }

    private function normalizeBarcodeValue(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/[^\\x20-\\x7E]/', '', $value);
        return trim($value);
    }

    private function normalizeHtTtc($htInput, $ttcInput, float $tvaRate, string $label): array
    {
        $ht = $htInput !== null && $htInput !== '' ? (float) $htInput : null;
        $ttc = $ttcInput !== null && $ttcInput !== '' ? (float) $ttcInput : null;
        $factor = 1 + max($tvaRate, 0) / 100;

        if ($ht === null && $ttc === null) {
            throw ValidationException::withMessages([
                "prix_{$label}_ttc" => "Saisissez le prix {$label} HT ou TTC.",
            ]);
        }

        if ($ht === null && $ttc !== null) {
            $ht = $factor > 0 ? $ttc / $factor : $ttc;
        }

        if ($ttc === null && $ht !== null) {
            $ttc = $ht * $factor;
        }

        return [round((float) $ht, 2), round((float) $ttc, 2)];
    }

    private function resolveReference(Request $request, ?string $currentReference = null): string
    {
        $manual = strtoupper(trim((string) $request->input('reference', '')));
        if ($manual !== '') {
            return $manual;
        }

        $type = strtoupper(trim((string) $request->input('reference_type', '')));
        $group = trim((string) $request->input('reference_group', ''));
        $line = strtoupper(trim((string) $request->input('reference_line', '')));
        $season = strtoupper(trim((string) $request->input('reference_season', '')));

        if ($type === '' && $group === '' && $line === '' && $season === '' && $currentReference) {
            return $currentReference;
        }

        if ($type === '' || $group === '' || $line === '' || $season === '') {
            throw ValidationException::withMessages([
                'reference' => "Saisissez une reference (ex: COS02001TH) ou remplissez Type/Groupe/Ligne/Saison.",
            ]);
        }

        $next = $this->getNextReferenceSequence($currentReference);
        return $type . $group . str_pad((string) $next, 3, '0', STR_PAD_LEFT) . $line . $season;
    }

    private function getNextReferenceSequence(?string $currentReference = null): int
    {
        $max = 0;

        $references = Product::query()
            ->when($currentReference, function ($q) use ($currentReference) {
                $q->where('reference', '!=', $currentReference);
            })
            ->pluck('reference');

        foreach ($references as $ref) {
            if (preg_match('/^[A-Z0-9]{3}\d{2}(\d{3})[A-Z0-9]{2,}$/', strtoupper((string) $ref), $m)) {
                $seq = (int) $m[1];
                if ($seq > $max) {
                    $max = $seq;
                }
            }
        }

        $next = $max + 1;
        if ($next > 999) {
            throw ValidationException::withMessages([
                'reference' => 'Limite atteinte: NNN depasse 999. Veuillez ajuster le format de reference.',
            ]);
        }

        return $next;
    }
}
