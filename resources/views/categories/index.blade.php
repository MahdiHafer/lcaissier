@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Gestion des categories</h2>

    <div class="card p-4 mb-4">
        <form method="POST" action="{{ route('categories.store') }}" class="row g-2">
            @csrf
            <div class="col-md-10">
                <input type="text" name="name" class="form-control" placeholder="Nom de la categorie" required>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Ajouter</button>
            </div>
        </form>
    </div>

    <div class="card p-3">
        <table class="table table-striped align-middle mb-0">
            <thead>
            <tr>
                <th>Nom</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>
                        <form method="POST" action="{{ route('categories.update', $category) }}" class="d-flex gap-2">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $category->name }}" class="form-control" required>
                            <button class="btn btn-outline-primary btn-sm">Sauver</button>
                        </form>
                    </td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Supprimer cette categorie ?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center text-muted">Aucune categorie</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

