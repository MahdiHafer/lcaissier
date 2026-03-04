@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Ajouter un nouveau client</h2>

    <form action="{{ route('clients.store') }}" method="POST" class="card p-4">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom <span class="text-danger">*</span></label>
                <input type="text" name="nom" class="form-control" required value="{{ old('nom') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Societe</label>
                <input type="text" name="societe" class="form-control" value="{{ old('societe') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">ICE</label>
                <input type="text" name="ice" class="form-control" value="{{ old('ice') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">RC</label>
                <input type="text" name="rc" class="form-control" value="{{ old('rc') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">IF</label>
                <input type="text" name="if_fiscal" class="form-control" value="{{ old('if_fiscal') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Telephone</label>
                <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Adresse</label>
                <input type="text" name="adresse" class="form-control" value="{{ old('adresse') }}">
            </div>

            <div class="col-12 text-end mt-2">
                <a href="{{ route('clients.index') }}" class="btn btn-light">Annuler</a>
                <button type="submit" class="btn btn-primary ms-2">Enregistrer</button>
            </div>
        </div>
    </form>
</div>
@endsection