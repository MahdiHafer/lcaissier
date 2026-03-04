@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Modifier le fournisseur</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('fournisseurs.update',$fournisseur->id) }}" method="POST" class="card p-4">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom du fournisseur</label>
                <input type="text" name="nom" class="form-control" value="{{ old('nom',$fournisseur->nom) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Type de fournisseur</label>
                <select name="societe" class="form-select" required>
                    <option value="Societe" {{ old('societe',$fournisseur->societe)=='Societe'?'selected':'' }}>Societe</option>
                    <option value="Client comptoir" {{ old('societe',$fournisseur->societe)=='Client comptoir'?'selected':'' }}>Client comptoir</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Numero de telephone</label>
                <input type="text" name="telephone" class="form-control" value="{{ old('telephone',$fournisseur->telephone) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="text" name="email" class="form-control" value="{{ old('email',$fournisseur->email) }}">
            </div>

            <div class="col-md-12">
                <label class="form-label">Adresse</label>
                <textarea name="adresse" class="form-control" rows="3">{{ old('adresse',$fournisseur->adresse) }}</textarea>
            </div>

            <div class="col-12 text-end mt-2">
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            </div>
        </div>
    </form>
</div>
@endsection