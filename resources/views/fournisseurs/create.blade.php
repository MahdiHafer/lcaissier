@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Ajouter un fournisseur</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('fournisseurs.store') }}" method="POST" class="card p-4">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom du fournisseur</label>
                <input type="text" name="nom" class="form-control" required value="{{ old('nom') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Type de fournisseur</label>
                <select name="societe" class="form-select" required>
                    <option value="">-- Selectionner --</option>
                    <option value="Societe" {{ old('societe') == 'Societe' ? 'selected' : '' }}>Societe</option>
                    <option value="Client comptoir" {{ old('societe') == 'Client comptoir' ? 'selected' : '' }}>Client comptoir</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Numero de telephone</label>
                <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="text" name="email" class="form-control" value="{{ old('email') }}">
            </div>

            <div class="col-md-12">
                <label class="form-label">Adresse</label>
                <textarea name="adresse" class="form-control" rows="3">{{ old('adresse') }}</textarea>
            </div>

            <div class="col-12 text-end mt-2">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </div>
    </form>
</div>
@endsection