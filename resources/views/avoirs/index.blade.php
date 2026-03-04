@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Avoirs Clients</h2>
    </div>

    <form method="GET" class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Numero avoir, client, ticket...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100">Rechercher</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('avoirs.index') }}" class="btn btn-light w-100">X</a>
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
                <th>Vente</th>
                <th>Total avoir</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($avoirs as $avoir)
                <tr>
                    <td>{{ $avoir->numero }}</td>
                    <td>{{ \Carbon\Carbon::parse($avoir->date_avoir)->format('d/m/Y') }}</td>
                    <td>{{ optional($avoir->client)->nom ?: 'Client comptoir' }}</td>
                    <td>{{ optional($avoir->vente)->numero_ticket ?: '-' }}</td>
                    <td class="fw-bold">{{ number_format($avoir->total, 2) }} DH</td>
                    <td class="text-end">
                        <a href="{{ route('avoirs.print', $avoir) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Imprimer</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Aucun avoir</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $avoirs->links() }}</div>
</div>
@endsection
