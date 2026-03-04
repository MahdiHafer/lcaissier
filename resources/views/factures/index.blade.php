@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Factures</h2>
        <a href="{{ route('bons-livraison.index') }}" class="btn btn-outline-secondary">Depuis BL</a>
    </div>

    <form method="GET" class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Numero facture, client...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100">Rechercher</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('factures.index') }}" class="btn btn-light w-100">X</a>
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
                <th>HT</th>
                <th>TVA</th>
                <th>TTC</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($factures as $facture)
                <tr>
                    <td>{{ $facture->numero }}</td>
                    <td>{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</td>
                    <td>{{ optional($facture->client)->nom ?: 'Client comptoir' }}</td>
                    <td>{{ number_format($facture->total_ht, 2) }} DH</td>
                    <td>{{ number_format($facture->tva_rate, 2) }}%</td>
                    <td class="fw-bold">{{ number_format($facture->total_ttc, 2) }} DH</td>
                    <td class="text-end">
                        <a href="{{ route('factures.show', $facture) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        <a href="{{ route('factures.print', $facture) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Imprimer</a>
                        <a href="{{ route('factures.edit', $facture) }}" class="btn btn-sm btn-outline-warning">Modifier</a>
                        <form method="POST" action="{{ route('factures.destroy', $facture) }}" class="d-inline" onsubmit="return confirm('Supprimer cette facture ?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Aucune facture</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $factures->links() }}
    </div>
</div>
@endsection