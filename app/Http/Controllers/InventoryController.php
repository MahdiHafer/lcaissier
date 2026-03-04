<?php

namespace App\Http\Controllers;

use App\InventoryLine;
use App\InventorySession;
use App\Product;
use App\ProductVariant;
use App\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $sessions = InventorySession::withCount('lines')->latest()->paginate(20);
        return view('inventory.index', compact('sessions'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_inventaire' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $session = InventorySession::create([
                'numero' => $this->generateNumero(),
                'date_inventaire' => $request->date_inventaire,
                'status' => 'brouillon',
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            $products = Product::with('variants')->orderBy('marque')->get();
            foreach ($products as $product) {
                if ($product->has_variants && $product->variants->count()) {
                    foreach ($product->variants as $variant) {
                        $session->lines()->create([
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'theoretical_qty' => (int) $variant->quantity,
                            'counted_qty' => (int) $variant->quantity,
                            'difference' => 0,
                        ]);
                    }
                } else {
                    $session->lines()->create([
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'theoretical_qty' => (int) $product->quantite,
                        'counted_qty' => (int) $product->quantite,
                        'difference' => 0,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('inventory.show', $session)->with('success', 'Session inventaire creee.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur creation inventaire: ' . $e->getMessage());
        }
    }

    public function show(InventorySession $inventory)
    {
        $inventory->load(['lines.product', 'lines.variant.color']);
        return view('inventory.show', compact('inventory'));
    }

    public function saveCounts(Request $request, InventorySession $inventory)
    {
        if ($inventory->status === 'valide') {
            return back()->with('error', 'Inventaire deja valide.');
        }

        $request->validate([
            'counted_qty' => 'required|array',
            'counted_qty.*' => 'nullable|integer|min:0',
            'reason' => 'nullable|array',
            'reason.*' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $inventory) {
            $inventory->lines()->each(function (InventoryLine $line) use ($request) {
                $counted = (int) ($request->input("counted_qty.{$line->id}") ?? $line->counted_qty);
                $line->counted_qty = $counted;
                $line->difference = $counted - (int) $line->theoretical_qty;
                $line->reason = trim((string) ($request->input("reason.{$line->id}") ?? '')) ?: null;
                $line->save();
            });
        });

        return back()->with('success', 'Comptage enregistre.');
    }

    public function validateInventory(Request $request, InventorySession $inventory)
    {
        if ($inventory->status === 'valide') {
            return back()->with('error', 'Inventaire deja valide.');
        }

        $request->validate([
            'counted_qty' => 'required|array',
            'counted_qty.*' => 'nullable|integer|min:0',
            'reason' => 'nullable|array',
            'reason.*' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $inventory->load('lines.product', 'lines.variant');
            $variantProductIds = [];

            foreach ($inventory->lines as $line) {
                $theoretical = (int) $line->theoretical_qty;
                $counted = (int) ($request->input("counted_qty.{$line->id}") ?? $line->counted_qty);
                $diff = $counted - $theoretical;

                $line->counted_qty = $counted;
                $line->difference = $diff;
                $line->reason = trim((string) ($request->input("reason.{$line->id}") ?? $line->reason)) ?: null;
                $line->save();

                if ($line->variant_id && $line->variant) {
                    $variant = $line->variant;
                    $before = (int) $variant->quantity;
                    if ($before !== $counted) {
                        $variant->quantity = $counted;
                        $variant->save();
                    }
                    $variantProductIds[] = (int) $variant->product_id;

                    if ($diff !== 0) {
                        StockMovement::create([
                            'product_id' => $line->product_id,
                            'variant_id' => $line->variant_id,
                            'movement_type' => 'inventory_adjustment',
                            'quantity_delta' => $diff,
                            'stock_before' => $before,
                            'stock_after' => $counted,
                            'source_type' => 'inventory_session',
                            'source_id' => $inventory->id,
                            'notes' => $line->reason,
                            'user_id' => auth()->id(),
                        ]);
                    }
                } elseif ($line->product_id && $line->product) {
                    $product = $line->product;
                    $before = (int) $product->quantite;
                    if ($before !== $counted) {
                        $product->quantite = $counted;
                        $product->save();
                    }

                    if ($diff !== 0) {
                        StockMovement::create([
                            'product_id' => $line->product_id,
                            'variant_id' => null,
                            'movement_type' => 'inventory_adjustment',
                            'quantity_delta' => $diff,
                            'stock_before' => $before,
                            'stock_after' => $counted,
                            'source_type' => 'inventory_session',
                            'source_id' => $inventory->id,
                            'notes' => $line->reason,
                            'user_id' => auth()->id(),
                        ]);
                    }
                }
            }

            foreach (array_unique($variantProductIds) as $productId) {
                $product = Product::with('variants')->find($productId);
                if ($product) {
                    $product->quantite = (int) $product->variants->sum('quantity');
                    $product->save();
                }
            }

            $inventory->status = 'valide';
            $inventory->save();

            DB::commit();
            return redirect()->route('inventory.index')->with('success', 'Inventaire valide et stock mis a jour.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Validation inventaire impossible: ' . $e->getMessage());
        }
    }

    private function generateNumero(): string
    {
        $today = now()->format('Ymd');
        $last = InventorySession::whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($last && preg_match('/INV-\d{8}-(\d+)/', $last->numero, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return 'INV-' . $today . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}