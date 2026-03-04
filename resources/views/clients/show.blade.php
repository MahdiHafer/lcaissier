@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="m-0">Dossier Client</h2>
            <div class="text-muted">{{ $client->nom }} @if($client->societe) | {{ $client->societe }} @endif</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-primary">Modifier client</a>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Retour</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-2">
            <div class="card p-3 h-100">
                <div class="text-muted small">CA client</div>
                <div class="fw-bold fs-5">{{ number_format($caTotal, 2) }} DH</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3 h-100">
                <div class="text-muted small">Total paye</div>
                <div class="fw-bold fs-5 text-success">{{ number_format($totalPaye, 2) }} DH</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3 h-100">
                <div class="text-muted small">Credit restant</div>
                <div class="fw-bold fs-5 {{ $totalCredit > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($totalCredit, 2) }} DH</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3 h-100">
                <div class="text-muted small">Total BL</div>
                <div class="fw-bold fs-5">{{ number_format($totalBl, 2) }} DH</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3 h-100">
                <div class="text-muted small">Total Factures</div>
                <div class="fw-bold fs-5">{{ number_format($totalFactures, 2) }} DH</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3 h-100">
                <div class="text-muted small">Mouvements</div>
                <div class="fw-bold fs-5">{{ $mouvements->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card p-3 mb-3">
        <h5 class="mb-3">Fiche Client</h5>
        <div class="row g-2">
            <div class="col-md-3"><strong>Nom:</strong> {{ $client->nom }}</div>
            <div class="col-md-3"><strong>Societe:</strong> {{ $client->societe ?: '-' }}</div>
            <div class="col-md-2"><strong>ICE:</strong> {{ $client->ice ?: '-' }}</div>
            <div class="col-md-2"><strong>RC:</strong> {{ $client->rc ?: '-' }}</div>
            <div class="col-md-2"><strong>IF:</strong> {{ $client->if_fiscal ?: '-' }}</div>
            <div class="col-md-3"><strong>Telephone:</strong> {{ $client->telephone ?: '-' }}</div>
            <div class="col-md-3"><strong>Email:</strong> {{ $client->email ?: '-' }}</div>
            <div class="col-md-6"><strong>Adresse:</strong> {{ $client->adresse ?: '-' }}</div>
        </div>
    </div>

    <div class="card p-3 mb-3">
        <h5 class="mb-3">Mouvements (Ventes / BL / Factures)</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Numero</th>
                    <th>Montant</th>
                    <th>Paye</th>
                    <th>Reste/Credit</th>
                    <th>Etat</th>
                </tr>
                </thead>
                <tbody>
                @forelse($mouvements as $m)
                    <tr>
                        <td>{{ optional($m['date'])->format('d/m/Y H:i') }}</td>
                        <td>{{ $m['type'] }}</td>
                        <td>{{ $m['numero'] }}</td>
                        <td>{{ number_format($m['montant'] ?? 0, 2) }} DH</td>
                        <td>{{ $m['paye'] !== null ? number_format($m['paye'], 2).' DH' : '-' }}</td>
                        <td>{{ $m['reste'] !== null ? number_format($m['reste'], 2).' DH' : '-' }}</td>
                        <td>{{ $m['status'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Aucun mouvement</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card p-3 h-100">
                <h5 class="mb-3">Historique Ventes</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Date</th>
                            <th>Net</th>
                            <th>Paye</th>
                            <th>Reste</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($ventes as $vente)
                            @php $reste = max((float)$vente->net_a_payer - (float)$vente->montant_paye, 0); @endphp
                            <tr>
                                <td>{{ $vente->numero_ticket }}</td>
                                <td>{{ $vente->created_at->format('d/m/Y') }}</td>
                                <td>{{ number_format($vente->net_a_payer, 2) }} DH</td>
                                <td>{{ number_format($vente->montant_paye, 2) }} DH</td>
                                <td class="{{ $reste > 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($reste, 2) }} DH</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Aucune vente</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card p-3 h-100">
                <h5 class="mb-3">Historique BL</h5>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Statut</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($bonsLivraison as $bon)
                            <tr>
                                <td>{{ $bon->numero }}</td>
                                <td>{{ \Carbon\Carbon::parse($bon->date_bon)->format('d/m/Y') }}</td>
                                <td>{{ number_format($bon->total, 2) }} DH</td>
                                <td>{{ ucfirst($bon->status ?? 'brouillon') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Aucun BL</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <h5 class="mb-3">Historique Factures</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Date</th>
                            <th>TVA</th>
                            <th>TTC</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($factures as $facture)
                            <tr>
                                <td><a href="{{ route('factures.show', $facture) }}">{{ $facture->numero }}</a></td>
                                <td>{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</td>
                                <td>{{ number_format($facture->tva_rate, 2) }}%</td>
                                <td>{{ number_format($facture->total_ttc, 2) }} DH</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Aucune facture</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection