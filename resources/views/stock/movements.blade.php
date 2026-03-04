@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Mouvements de stock</h2>
        <a href="{{ route('stock.dashboard') }}" class="btn btn-outline-secondary">Retour stock</a>
    </div>

    <form method="GET" class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Produit, type mouvement, note...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filtrer</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('stock.movements') }}" class="btn btn-light w-100">X</a>
            </div>
        </div>
    </form>

    <div class="table-responsive card p-2">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Date</th>
                <th>Produit</th>
                <th>Variante</th>
                <th>Type</th>
                <th>Delta</th>
                <th>Avant</th>
                <th>Apres</th>
                <th>Source</th>
                <th>Note</th>
            </tr>
            </thead>
            <tbody>
            @forelse($movements as $m)
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ optional($m->product)->marque ?: '-' }}</td>
                    <td>
                        @if($m->variant)
                            {{ $m->variant->size ?: '-' }} / {{ optional($m->variant->color)->name ?: '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $m->movement_type }}</td>
                    <td class="{{ $m->quantity_delta >= 0 ? 'text-success' : 'text-danger' }} fw-bold">{{ $m->quantity_delta > 0 ? '+' : '' }}{{ $m->quantity_delta }}</td>
                    <td>{{ $m->stock_before ?? '-' }}</td>
                    <td>{{ $m->stock_after ?? '-' }}</td>
                    <td>{{ $m->source_type ?: '-' }} {{ $m->source_id ? '#'.$m->source_id : '' }}</td>
                    <td>{{ $m->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted">Aucun mouvement</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $movements->links() }}</div>
</div>
@endsection