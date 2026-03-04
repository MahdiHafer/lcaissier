@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestion des fournisseurs</h2>
        <a href="{{ route('fournisseurs.create') }}" class="btn btn-primary">+ Ajouter un fournisseur</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('fournisseurs.index') }}" class="card p-3 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" value="{{ request('nom') }}" class="form-control" placeholder="Chercher par nom">
            </div>
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">-- Tous les types --</option>
                    <option value="Societe" {{ request('type') == 'Societe' ? 'selected' : '' }}>Societe</option>
                    <option value="Client comptoir" {{ request('type') == 'Client comptoir' ? 'selected' : '' }}>Client comptoir</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary">Rechercher</button>
            </div>
        </div>
    </form>

    <div class="table-responsive card p-2">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Telephone</th>
                    <th>Email</th>
                    <th>Adresse</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($fournisseurs as $fournisseur)
                <tr>
                    <td>{{ $fournisseur->nom }}</td>
                    <td>{{ $fournisseur->societe ?: '-' }}</td>
                    <td>{{ $fournisseur->telephone ?: '-' }}</td>
                    <td>{{ $fournisseur->email ?: '-' }}</td>
                    <td>{{ $fournisseur->adresse ?: '-' }}</td>
                    <td class="text-end">
                        <a href="{{ route('fournisseurs.edit',$fournisseur) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <form action="{{ route('fournisseurs.destroy',$fournisseur) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce fournisseur ?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Aucun fournisseur trouve.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection