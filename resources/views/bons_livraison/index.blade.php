@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Bons de livraison</h2>
        <a href="{{ route('bons-livraison.create') }}" class="btn btn-primary">Nouveau bon</a>
    </div>

    <form method="GET" class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Numero BL, client...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100">Rechercher</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('bons-livraison.index') }}" class="btn btn-light w-100">X</a>
            </div>
        </div>
    </form>

    <div class="table-responsive card p-2">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Numero</th>
                <th>Date</th>
                <th>Client</th>
                <th>Total</th>
                <th>Statut</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($bons as $bon)
                <tr>
                    <td>{{ $bon->numero }}</td>
                    <td>{{ \Carbon\Carbon::parse($bon->date_bon)->format('d/m/Y') }}</td>
                    <td>{{ optional($bon->client)->nom ?: 'Client comptoir' }}</td>
                    <td>{{ number_format($bon->total, 2) }} DH</td>
                    <td>
                        @php
                            $badge = 'bg-secondary';
                            if ($bon->status === 'partiel') { $badge = 'bg-warning text-dark'; }
                            if ($bon->status === 'livre') { $badge = 'bg-success'; }
                        @endphp
                        <span class="badge {{ $badge }}">{{ ucfirst($bon->status ?? 'brouillon') }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('bons-livraison.convert', $bon) }}" class="btn btn-sm btn-outline-success">Passer en vente</a>
                        <a href="{{ route('bons-livraison.print', $bon) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Imprimer</a>
                        <a href="{{ route('bons-livraison.edit', $bon) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <form method="POST" action="{{ route('bons-livraison.destroy', $bon) }}" class="d-inline" onsubmit="return confirm('Supprimer ce bon de livraison ?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Aucun bon de livraison</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $bons->links() }}
    </div>
</div>
@endsection
