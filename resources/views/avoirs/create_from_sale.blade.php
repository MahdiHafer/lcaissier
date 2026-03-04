@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Retour produit -> Avoir</h2>
        <a href="{{ route('ventes.historique') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="card p-3 mb-3">
        <div class="row">
            <div class="col-md-3"><strong>Ticket:</strong> {{ $vente->numero_ticket }}</div>
            <div class="col-md-3"><strong>Date:</strong> {{ $vente->created_at->format('d/m/Y H:i') }}</div>
            <div class="col-md-3"><strong>Client:</strong> {{ optional($vente->clientInfo)->nom ?: 'Client comptoir' }}</div>
            <div class="col-md-3 text-md-end"><strong>Net vente:</strong> {{ number_format($vente->net_a_payer, 2) }} DH</div>
        </div>
    </div>

    <form method="POST" action="{{ route('ventes.avoir.store', $vente) }}">
        @csrf

        <div class="table-responsive card p-2">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th style="width:50px;"><input type="checkbox" id="checkAll"></th>
                    <th>Produit</th>
                    <th>Qte vendue</th>
                    <th>Deja retournee</th>
                    <th>Reste retour possible</th>
                    <th>Qte retour</th>
                    <th>PU</th>
                </tr>
                </thead>
                <tbody>
                @forelse($vente->details as $line)
                    @php
                        $sold = max((int)$line->quantite, 0);
                        $returned = max((int)($line->quantite_retournee ?? 0), 0);
                        $remaining = max($sold - $returned, 0);
                    @endphp
                    <tr>
                        <td>
                            @if($remaining > 0)
                                <input type="checkbox" class="line-check" name="detail_ids[]" value="{{ $line->id }}">
                            @endif
                        </td>
                        <td>
                            {{ $line->nom_produit }}
                            <div class="small text-muted">Ref: {{ $line->reference_produit }}</div>
                        </td>
                        <td>{{ $sold }}</td>
                        <td>{{ $returned }}</td>
                        <td>{{ $remaining }}</td>
                        <td style="max-width:120px;">
                            @if($remaining > 0)
                                <input type="number" min="0" max="{{ $remaining }}" value="0" class="form-control qte-input" name="return_qty[{{ $line->id }}]">
                            @else
                                <span class="badge bg-secondary">Aucun retour</span>
                            @endif
                        </td>
                        <td>{{ number_format($line->prix_unitaire, 2) }} DH</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Aucune ligne</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card p-3 mt-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="2" placeholder="Motif du retour, remarque..."></textarea>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-warning">Generer avoir + remettre stock</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
const checkAll = document.getElementById('checkAll');
const checks = Array.from(document.querySelectorAll('.line-check'));
checkAll?.addEventListener('change', () => {
    checks.forEach((c) => {
        c.checked = checkAll.checked;
        const qty = c.closest('tr').querySelector('.qte-input');
        if (qty && c.checked && Number(qty.value || 0) === 0) qty.value = 1;
    });
});
checks.forEach((c) => {
    c.addEventListener('change', () => {
        const qty = c.closest('tr').querySelector('.qte-input');
        if (qty && c.checked && Number(qty.value || 0) === 0) qty.value = 1;
    });
});
</script>
@endsection