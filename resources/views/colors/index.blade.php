@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Gestion des couleurs</h2>

    <div class="card p-4 mb-4">
        <form method="POST" action="{{ route('colors.store') }}" class="row g-2">
            @csrf
            <div class="col-md-5">
                <input type="text" name="name" class="form-control" placeholder="Nom de la couleur" required>
            </div>
            <div class="col-md-3">
                <input type="color" name="hex_code" class="form-control form-control-color w-100" value="#000000">
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100">Ajouter</button>
            </div>
        </form>
    </div>

    <div class="card p-3">
        <table class="table table-striped align-middle mb-0">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Code</th>
                <th>Apercu</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($colors as $color)
                <tr>
                    <td>
                        <form method="POST" action="{{ route('colors.update', $color) }}" class="row g-2">
                            @csrf
                            @method('PUT')
                            <div class="col-md-6">
                                <input type="text" name="name" value="{{ $color->name }}" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <input type="color" name="hex_code" value="{{ $color->hex_code ?: '#000000' }}" class="form-control form-control-color w-100">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-primary btn-sm w-100">Sauver</button>
                            </div>
                        </form>
                    </td>
                    <td>{{ $color->hex_code ?: '-' }}</td>
                    <td>
                        <span style="display:inline-block;width:22px;height:22px;border:1px solid #ddd;border-radius:50%;background:{{ $color->hex_code ?: '#ffffff' }}"></span>
                    </td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('colors.destroy', $color) }}" class="d-inline" onsubmit="return confirm('Supprimer cette couleur ?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Aucune couleur</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

