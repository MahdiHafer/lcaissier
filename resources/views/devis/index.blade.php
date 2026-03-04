@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Devis</h2>
        <a href="{{ route('devis.create') }}" class="btn btn-primary">Nouveau devis</a>
    </div>

    <form method="GET" class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Numero devis, client...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100">Rechercher</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('devis.index') }}" class="btn btn-light w-100">X</a>
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
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($devis as $d)
                <tr>
                    <td>{{ $d->numero }}</td>
                    <td>{{ \Carbon\Carbon::parse($d->date_devis)->format('d/m/Y') }}</td>
                    <td>{{ optional($d->client)->nom ?: 'Client comptoir' }}</td>
                    <td>{{ number_format($d->total, 2) }} DH</td>
                    <td class="text-end">
                        <a href="{{ route('devis.print', $d) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Imprimer</a>
                        <a href="{{ route('devis.edit', $d) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <form method="POST" action="{{ route('devis.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Supprimer ce devis ?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Aucun devis</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $devis->links() }}
    </div>
</div>
@endsection