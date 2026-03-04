@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Modifier facture {{ $facture->numero }}</h2>

    <form method="POST" action="{{ route('factures.update', $facture) }}" class="card p-4">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Date facture</label>
                <input type="date" name="date_facture" class="form-control" value="{{ old('date_facture', \Carbon\Carbon::parse($facture->date_facture)->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Client</label>
                <select name="client_id" class="form-select">
                    <option value="">Client comptoir</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ (string)old('client_id', $facture->client_id) === (string)$client->id ? 'selected' : '' }}>{{ $client->nom }}</option>
                    @endforeach
                </select>
                @if($facture->client_id)
                    <div class="mt-1">
                        <a href="{{ route('clients.edit', $facture->client_id) }}" class="small">Modifier les infos juridiques du client</a>
                    </div>
                @endif
            </div>
            <div class="col-md-2">
                <label class="form-label">TVA %</label>
                <input type="number" step="0.01" min="0" max="100" name="tva_rate" class="form-control" value="{{ old('tva_rate', $facture->tva_rate) }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Societe</label>
                <input type="text" name="legal_company_name" class="form-control" value="{{ old('legal_company_name', $facture->legal_company_name) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">ICE</label>
                <input type="text" name="legal_ice" class="form-control" value="{{ old('legal_ice', $facture->legal_ice) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">RC</label>
                <input type="text" name="legal_rc" class="form-control" value="{{ old('legal_rc', $facture->legal_rc) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">IF</label>
                <input type="text" name="legal_if" class="form-control" value="{{ old('legal_if', $facture->legal_if) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">CNSS</label>
                <input type="text" name="legal_cnss" class="form-control" value="{{ old('legal_cnss', $facture->legal_cnss) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Telephone</label>
                <input type="text" name="legal_phone" class="form-control" value="{{ old('legal_phone', $facture->legal_phone) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="text" name="legal_email" class="form-control" value="{{ old('legal_email', $facture->legal_email) }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Adresse</label>
                <input type="text" name="legal_address" class="form-control" value="{{ old('legal_address', $facture->legal_address) }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $facture->notes) }}</textarea>
            </div>

            <div class="col-12 text-end">
                <button class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('factures.show', $facture) }}" class="btn btn-light">Annuler</a>
            </div>
        </div>
    </form>
</div>
@endsection
