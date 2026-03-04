@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Inventaires</h2>
        <a href="{{ route('inventory.create') }}" class="btn btn-primary">Nouvel inventaire</a>
    </div>

    <div class="table-responsive card p-2">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Numero</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Lignes</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($sessions as $s)
                <tr>
                    <td>{{ $s->numero }}</td>
                    <td>{{ \Carbon\Carbon::parse($s->date_inventaire)->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge {{ $s->status === 'valide' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($s->status) }}</span>
                    </td>
                    <td>{{ $s->lines_count }}</td>
                    <td class="text-end">
                        <a href="{{ route('inventory.show', $s) }}" class="btn btn-sm btn-outline-primary">Ouvrir</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Aucun inventaire</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $sessions->links() }}</div>
</div>
@endsection