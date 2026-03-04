@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Historique des ventes</h2>

    <form method="GET" action="{{ route('ventes.historique') }}" class="card p-3 mb-4">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="client" value="{{ request('client') }}" class="form-control" placeholder="Client">
            </div>
            <div class="col-md-3">
                <input type="text" name="fournisseur" value="{{ request('fournisseur') }}" class="form-control" placeholder="Fournisseur">
            </div>
            <div class="col-md-2">
                <input type="text" name="produit" value="{{ request('produit') }}" class="form-control" placeholder="Produit">
            </div>
            <div class="col-md-2">
                <input type="date" name="date" value="{{ request('date') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <select name="paiement" class="form-select">
                    <option value="">Paiement</option>
                    <option value="Especes" {{ request('paiement') === 'Especes' ? 'selected' : '' }}>Especes</option>
                    <option value="Virement" {{ request('paiement') === 'Virement' ? 'selected' : '' }}>Virement</option>
                    <option value="Credit" {{ request('paiement') === 'Credit' ? 'selected' : '' }}>Credit</option>
                    <option value="Cheque" {{ request('paiement') === 'Cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="TPE" {{ request('paiement') === 'TPE' ? 'selected' : '' }}>TPE</option>
                    <option value="Avoir" {{ request('paiement') === 'Avoir' ? 'selected' : '' }}>Avoir</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Rechercher</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('ventes.historique') }}" class="btn btn-light w-100">X</a>
            </div>
        </div>
    </form>

    @if(auth()->user()->role === 'admin')
        <div class="alert alert-info text-center">Total net: <strong>{{ number_format($totalNet, 2) }} DH</strong></div>
    @endif

    <div class="table-responsive card p-2">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Ticket</th>
                <th>Date</th>
                <th>Client</th>
                <th>Total</th>
                <th>Remise</th>
                <th>Net</th>
                <th>Paiement</th>
                <th>Paye</th>
                <th>Reste</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($ventes as $vente)
                <tr>
                    <td>{{ $vente->numero_ticket }}</td>
                    <td>{{ $vente->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($vente->clientInfo)
                            {{ $vente->clientInfo->nom }}
                            <div class="small text-muted">{{ $vente->clientInfo->telephone }}</div>
                        @else
                            <span class="text-muted">Comptoir</span>
                        @endif
                    </td>
                    <td>{{ number_format($vente->total, 2) }} DH</td>
                    <td>{{ number_format($vente->remise, 2) }} DH</td>
                    <td class="fw-bold text-success">{{ number_format($vente->net_a_payer, 2) }} DH</td>
                    <td>{{ $vente->mode_paiement }}</td>
                    <td>{{ number_format($vente->montant_paye, 2) }} DH</td>
                    <td class="{{ $vente->net_a_payer - $vente->montant_paye > 0 ? 'text-warning fw-bold' : '' }}">{{ number_format($vente->net_a_payer - $vente->montant_paye, 2) }} DH</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $vente->id }}">Details</button>
                        <a href="{{ route('ventes.avoir.create', $vente) }}" class="btn btn-sm btn-outline-warning">Avoir</a>
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('ventes.edit', $vente->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('ventes.destroy', $vente->id) }}" class="d-inline" onsubmit="return confirm('Supprimer cette vente ?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted">Aucune vente</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $ventes->links() }}</div>
</div>

@foreach($ventes as $vente)
<div class="modal fade" id="detailsModal{{ $vente->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Details ticket {{ $vente->numero_ticket }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantite</th>
                        <th>PU</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($vente->details as $d)
                        <tr>
                            <td>{{ $d->nom_produit }}<div class="small text-muted">Ref: {{ $d->reference_produit }}</div></td>
                            <td>{{ $d->quantite }}</td>
                            <td>{{ number_format($d->prix_unitaire, 2) }} DH</td>
                            <td>{{ number_format($d->total_ligne, 2) }} DH</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
