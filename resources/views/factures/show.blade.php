@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Facture {{ $facture->numero }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('factures.print', $facture) }}" target="_blank" class="btn btn-outline-secondary">Imprimer</a>
            <a href="{{ route('factures.index') }}" class="btn btn-outline-dark">Retour</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</div>
                <div><strong>Client:</strong> {{ optional($facture->client)->nom ?: 'Client comptoir' }}</div>
                <div><strong>Societe:</strong> {{ optional($facture->client)->societe ?: '-' }}</div>
                <div><strong>ICE/RC/IF:</strong> {{ optional($facture->client)->ice ?: '-' }} / {{ optional($facture->client)->rc ?: '-' }} / {{ optional($facture->client)->if_fiscal ?: '-' }}</div>
                <div><strong>Telephone:</strong> {{ optional($facture->client)->telephone ?: '-' }}</div>
                <div><strong>Adresse:</strong> {{ optional($facture->client)->adresse ?: '-' }}</div>
                @if($facture->client_id)
                    <div class="mt-2"><a href="{{ route('clients.edit', $facture->client_id) }}" class="btn btn-sm btn-outline-primary">Modifier infos juridiques client</a></div>
                @endif
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-3 h-100">
                <div class="row g-2">
                    <div class="col-md-4"><strong>Societe:</strong> {{ $facture->legal_company_name ?: '-' }}</div>
                    <div class="col-md-4"><strong>ICE:</strong> {{ $facture->legal_ice ?: '-' }}</div>
                    <div class="col-md-4"><strong>RC:</strong> {{ $facture->legal_rc ?: '-' }}</div>
                    <div class="col-md-4"><strong>IF:</strong> {{ $facture->legal_if ?: '-' }}</div>
                    <div class="col-md-4"><strong>CNSS:</strong> {{ $facture->legal_cnss ?: '-' }}</div>
                    <div class="col-md-4"><strong>Contact:</strong> {{ $facture->legal_phone ?: '-' }}</div>
                    <div class="col-md-12"><strong>Adresse:</strong> {{ $facture->legal_address ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive card p-2 mb-3">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Designation</th>
                <th class="text-end">Qte</th>
                <th class="text-end">Prix</th>
                <th class="text-end">Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($facture->details as $line)
                <tr>
                    <td>{{ $line->designation }}</td>
                    <td class="text-end">{{ $line->quantite }}</td>
                    <td class="text-end">{{ number_format($line->prix_unitaire, 2) }} DH</td>
                    <td class="text-end">{{ number_format($line->total_ligne, 2) }} DH</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card p-3 ms-auto" style="max-width: 420px;">
        <div class="d-flex justify-content-between mb-1"><span>Total HT</span><strong>{{ number_format($facture->total_ht, 2) }} DH</strong></div>
        <div class="d-flex justify-content-between mb-1"><span>TVA ({{ number_format($facture->tva_rate, 2) }}%)</span><strong>{{ number_format($facture->tva_amount, 2) }} DH</strong></div>
        <div class="d-flex justify-content-between fs-5"><span>Total TTC</span><strong>{{ number_format($facture->total_ttc, 2) }} DH</strong></div>
    </div>
</div>
@endsection
