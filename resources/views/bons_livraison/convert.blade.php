@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">BL {{ $bon->numero }} -> Vente</h2>
        <a href="{{ route('bons-livraison.index') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="card p-3 mb-3">
        <div class="row">
            <div class="col-md-3"><strong>Date:</strong> {{ \Carbon\Carbon::parse($bon->date_bon)->format('d/m/Y') }}</div>
            <div class="col-md-5"><strong>Client:</strong> {{ optional($bon->client)->nom ?: 'Client comptoir' }}</div>
            <div class="col-md-4 text-md-end"><strong>Total BL:</strong> {{ number_format($bon->total, 2) }} DH</div>
        </div>
    </div>

    <form method="POST" action="{{ route('bons-livraison.convert.store', $bon) }}" id="convertForm">
        @csrf

        <div class="table-responsive card p-2">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th style="width:50px;"><input type="checkbox" id="checkAllLines"></th>
                    <th>Image</th>
                    <th>Designation</th>
                    <th>Qte BL</th>
                    <th>Deja vendue</th>
                    <th>Reste</th>
                    <th>Qte a vendre</th>
                    <th>Prix</th>
                    <th>Total ligne</th>
                </tr>
                </thead>
                <tbody>
                @forelse($bon->details as $line)
                    @php
                        $sold = (int) $line->quantite_vendue;
                        $remaining = max((int)$line->quantite - $sold, 0);
                    @endphp
                    <tr data-price="{{ (float) $line->prix_unitaire }}">
                        <td>
                            @if($remaining > 0)
                                <input type="checkbox" class="line-check" name="detail_ids[]" value="{{ $line->id }}">
                            @endif
                        </td>
                        <td>
                            @if($line->image)
                                <img src="{{ asset($line->image) }}" alt="" style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid #ddd;">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $line->designation }}</td>
                        <td>{{ $line->quantite }}</td>
                        <td>{{ $sold }}</td>
                        <td class="remaining">{{ $remaining }}</td>
                        <td style="width:130px;">
                            @if($remaining > 0)
                                <input type="number" min="0" max="{{ $remaining }}" step="1" value="0" name="sell_qty[{{ $line->id }}]" class="form-control sell-qty">
                            @else
                                <span class="badge bg-secondary">Deja livre</span>
                            @endif
                        </td>
                        <td>{{ number_format($line->prix_unitaire, 2) }} DH</td>
                        <td class="line-total">{{ number_format($line->prix_unitaire * $remaining, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Aucune ligne dans ce BL</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card p-3 mt-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Mode de paiement</label>
                    <select name="mode_paiement" class="form-select" id="modePaiement" required>
                        <option value="Especes">Especes</option>
                        <option value="Virement">Virement</option>
                        <option value="Credit">Credit</option>
                        <option value="Cheque">Cheque</option>
                        <option value="TPE">TPE</option>
                        <option value="Avoir">Avoir</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">TVA %</label>
                    <input type="number" step="0.01" min="0" max="100" name="tva_rate" id="tvaRateInput" class="form-control" value="20">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Remise</label>
                    <input type="number" step="0.01" min="0" name="remise" id="remiseInput" class="form-control" value="0">
                </div>
                <div class="col-md-3" id="montantPayeWrap" style="display:none;">
                    <label class="form-label">Montant paye (credit)</label>
                    <input type="number" step="0.01" min="0" name="montant_paye" class="form-control" value="0">
                </div>
                <div class="col-md-2 text-md-end">
                    <div><strong>Total HT:</strong> <span id="selectedTotal">0.00</span> DH</div>
                    <div><strong>TVA:</strong> <span id="tvaAmount">0.00</span> DH</div>
                    <div><strong>TTC:</strong> <span id="netTotal">0.00</span> DH</div>
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="submit" class="btn btn-success">Convertir la selection en vente + facture</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
const checkAll = document.getElementById('checkAllLines');
const lineChecks = Array.from(document.querySelectorAll('.line-check'));
const selectedTotalEl = document.getElementById('selectedTotal');
const tvaAmountEl = document.getElementById('tvaAmount');
const netTotalEl = document.getElementById('netTotal');
const remiseInput = document.getElementById('remiseInput');
const tvaRateInput = document.getElementById('tvaRateInput');
const modePaiement = document.getElementById('modePaiement');
const montantPayeWrap = document.getElementById('montantPayeWrap');

function computeTotals() {
    let total = 0;

    lineChecks.forEach((check) => {
        const row = check.closest('tr');
        const qtyInput = row.querySelector('.sell-qty');
        const max = Number(qtyInput?.max || 0);
        let qty = Number(qtyInput?.value || 0);

        if (qty > max) {
            qty = max;
            qtyInput.value = String(max);
        }
        if (qty < 0) {
            qty = 0;
            qtyInput.value = '0';
        }

        if (check.checked && qty > 0) {
            const pu = Number(row.dataset.price || 0);
            total += pu * qty;
        }
    });

    const remise = Number(remiseInput.value || 0);
    const ht = Math.max(total - remise, 0);
    const tvaRate = Number(tvaRateInput.value || 0);
    const tvaAmount = ht * (tvaRate / 100);
    const ttc = ht + tvaAmount;

    selectedTotalEl.textContent = ht.toFixed(2);
    tvaAmountEl.textContent = tvaAmount.toFixed(2);
    netTotalEl.textContent = ttc.toFixed(2);
}

checkAll?.addEventListener('change', () => {
    lineChecks.forEach((check) => {
        check.checked = checkAll.checked;
        const row = check.closest('tr');
        const qtyInput = row.querySelector('.sell-qty');
        if (qtyInput && check.checked && Number(qtyInput.value || 0) === 0) {
            qtyInput.value = '1';
        }
    });
    computeTotals();
});

lineChecks.forEach((check) => {
    check.addEventListener('change', () => {
        const row = check.closest('tr');
        const qtyInput = row.querySelector('.sell-qty');
        if (qtyInput && check.checked && Number(qtyInput.value || 0) === 0) {
            qtyInput.value = '1';
        }
        computeTotals();
    });

    const row = check.closest('tr');
    const qtyInput = row.querySelector('.sell-qty');
    qtyInput?.addEventListener('input', () => {
        if (Number(qtyInput.value || 0) > 0) {
            check.checked = true;
        }
        computeTotals();
    });
});

remiseInput.addEventListener('input', computeTotals);
tvaRateInput.addEventListener('input', computeTotals);

modePaiement.addEventListener('change', () => {
    montantPayeWrap.style.display = modePaiement.value === 'Credit' ? '' : 'none';
});

document.getElementById('convertForm').addEventListener('submit', (e) => {
    const selected = lineChecks.filter((c) => c.checked && Number(c.closest('tr').querySelector('.sell-qty')?.value || 0) > 0).length;
    if (!selected) {
        e.preventDefault();
        alert('Selectionnez au moins un article avec une quantite.');
    }
});

computeTotals();
</script>
@endsection