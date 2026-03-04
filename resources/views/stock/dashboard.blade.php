@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Stock - Pilotage</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-primary">Inventaires</a>
            <a href="{{ route('stock.movements') }}" class="btn btn-outline-secondary">Mouvements</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Produits</div><div class="fs-4 fw-bold">{{ $summary['total_products'] }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Unites stock</div><div class="fs-4 fw-bold">{{ $summary['total_units'] }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Valeur stock</div><div class="fs-4 fw-bold">{{ number_format($summary['stock_value'], 2) }} DH</div></div></div>
        <div class="col-md-3"><div class="card p-3"><div class="small text-muted">Rupture</div><div class="fs-4 fw-bold text-danger">{{ $summary['rupture_count'] }}</div></div></div>
    </div>

    <form method="GET" class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Produit, reference, code-barres...">
            </div>
            <div class="col-md-2 form-check d-flex align-items-center ms-2">
                <input class="form-check-input me-2" type="checkbox" name="rupture" value="1" id="ruptureCheck" {{ request('rupture') ? 'checked' : '' }}>
                <label class="form-check-label" for="ruptureCheck">Rupture uniquement</label>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filtrer</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('stock.dashboard') }}" class="btn btn-light w-100">X</a>
            </div>
        </div>
    </form>

    <div class="table-responsive card p-2">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Produit</th>
                <th>Reference</th>
                <th>Code-barres</th>
                <th>Categorie</th>
                <th>Stock global</th>
                <th>Variantes</th>
            </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->marque }}</td>
                    <td>{{ $product->reference ?: '-' }}</td>
                    <td>{{ $product->codebar ?: '-' }}</td>
                    <td>{{ optional($product->category)->name ?: ($product->categorie ?: '-') }}</td>
                    <td class="{{ (int)$product->quantite <= 0 ? 'text-danger fw-bold' : '' }}">{{ $product->quantite }}</td>
                    <td>
                        @if($product->variants->count())
                            @foreach($product->variants as $variant)
                                <div class="small">{{ $variant->size ?: '-' }} / {{ optional($variant->color)->name ?: '-' }} : {{ $variant->quantity }}</div>
                            @endforeach
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Aucun produit</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $products->links() }}</div>
</div>
@endsection