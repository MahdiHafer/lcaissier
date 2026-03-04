<?php

namespace App\Http\Controllers;

use App\Product;
use App\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = Product::with('variants.color', 'category')->orderBy('marque');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('marque', 'like', "%$search%")
                    ->orWhere('codebar', 'like', "%$search%")
                    ->orWhere('reference', 'like', "%$search%");
            });
        }

        if ($request->filled('rupture')) {
            $query->where('quantite', '<=', 0);
        }

        $products = $query->paginate(30)->appends($request->all());

        $summary = [
            'total_products' => Product::count(),
            'total_units' => (int) Product::sum('quantite'),
            'stock_value' => (float) Product::sum(DB::raw('prix_achat * quantite')),
            'rupture_count' => Product::where('quantite', '<=', 0)->count(),
        ];

        return view('stock.dashboard', compact('products', 'summary'));
    }

    public function movements(Request $request)
    {
        $query = StockMovement::with('product', 'variant.color')->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('movement_type', 'like', "%$search%")
                    ->orWhere('notes', 'like', "%$search%")
                    ->orWhereHas('product', function ($qp) use ($search) {
                        $qp->where('marque', 'like', "%$search%")
                            ->orWhere('reference', 'like', "%$search%")
                            ->orWhere('codebar', 'like', "%$search%");
                    });
            });
        }

        $movements = $query->paginate(40)->appends($request->all());
        return view('stock.movements', compact('movements'));
    }
}