@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Modifier le client</h2>

    <div class="card p-4">
        <form action="{{ route('clients.update', $client) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nom complet *</label>
                    <input type="text" class="form-control" name="nom" value="{{ old('nom', $client->nom) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Societe</label>
                    <input type="text" class="form-control" name="societe" value="{{ old('societe', $client->societe) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">ICE</label>
                    <input type="text" class="form-control" name="ice" value="{{ old('ice', $client->ice) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">RC</label>
                    <input type="text" class="form-control" name="rc" value="{{ old('rc', $client->rc) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">IF</label>
                    <input type="text" class="form-control" name="if_fiscal" value="{{ old('if_fiscal', $client->if_fiscal) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Telephone</label>
                    <input type="text" class="form-control" name="telephone" value="{{ old('telephone', $client->telephone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email', $client->email) }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Adresse</label>
                    <input type="text" class="form-control" name="adresse" value="{{ old('adresse', $client->adresse) }}">
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('clients.index') }}" class="btn btn-light px-4">Retour</a>
                    <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection