@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Devis {{ $devis->numero }} -> Facture</h2>
        <a href="{{ route('devis.index') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-3"><strong>Devis:</strong> {{ $devis->numero }}</div>
            <div class="col-md-3"><strong>Date:</strong> {{ \Carbon\Carbon::parse($devis->date_devis)->format('d/m/Y') }}</div>
            <div class="col-md-4"><strong>Client:</strong> {{ optional($devis->client)->nom ?: 'Client comptoir' }}</div>
            <div class="col-md-2 text-md-end"><strong>Total devis:</strong> {{ number_format($devis->total, 2) }} DH</div>
        </div>
    </div>

    <form method="POST" action="{{ route('devis.facture.store', $devis) }}" class="card p-3" id="createInvoiceForm">
        @csrf

        <div class="row g-2 align-items-end mb-3">
            <div class="col-md-3">
                <label class="form-label">Date facture</label>
                <input type="date" name="date_facture" class="form-control" value="{{ old('date_facture', now()->toDateString()) }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">TVA % (incluse)</label>
                <input type="number" step="0.01" min="0" max="100" name="tva_rate" id="tvaRateInput" class="form-control" value="{{ old('tva_rate', 20) }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Remise</label>
                <input type="number" step="0.01" min="0" name="remise_value" id="remiseValueInput" class="form-control" value="{{ old('remise_value', 0) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Type remise</label>
                <select name="remise_type" id="remiseTypeInput" class="form-select">
                    <option value="dh" {{ old('remise_type', 'dh') === 'dh' ? 'selected' : '' }}>DH</option>
                    <option value="%" {{ old('remise_type') === '%' ? 'selected' : '' }}>%</option>
                </select>
            </div>
            <div class="col-md-3 text-md-end">
                <div><strong>Total HT:</strong> <span id="totalHt">0.00</span> DH</div>
                <div><strong>TVA:</strong> <span id="tvaAmount">0.00</span> DH</div>
                <div><strong>Total TTC:</strong> <span id="totalTtc">0.00</span> DH</div>
            </div>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>Designation</th>
                    <th class="text-end">Qte</th>
                    <th class="text-end">PU</th>
                    <th class="text-end">Total ligne</th>
                </tr>
                </thead>
                <tbody>
                @foreach($devis->details as $line)
                    <tr>
                        <td>{{ $line->designation }}</td>
                        <td class="text-end">{{ $line->quantite }}</td>
                        <td class="text-end">{{ number_format($line->prix_unitaire, 2) }} DH</td>
                        <td class="text-end line-total" data-value="{{ (float) $line->total_ligne }}">{{ number_format($line->total_ligne, 2) }} DH</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
        </div>

        <div class="text-end">
            <button class="btn btn-success">Creer facture</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
const tvaRateInput = document.getElementById('tvaRateInput');
const remiseValueInput = document.getElementById('remiseValueInput');
const remiseTypeInput = document.getElementById('remiseTypeInput');
const totalTtcEl = document.getElementById('totalTtc');
const totalHtEl = document.getElementById('totalHt');
const tvaAmountEl = document.getElementById('tvaAmount');

function computeInvoiceTotals() {
    let base = 0;
    document.querySelectorAll('.line-total').forEach((el) => {
        base += Number(el.dataset.value || 0);
    });

    const tvaRate = Math.max(Number(tvaRateInput.value || 0), 0);
    const factor = 1 + (tvaRate / 100);
    const baseHt = factor > 0 ? (base / factor) : base;
    const remiseRaw = Math.max(Number(remiseValueInput.value || 0), 0);
    const remiseHt = remiseTypeInput.value === '%' ? baseHt * (remiseRaw / 100) : remiseRaw;
    const ht = Math.max(baseHt - remiseHt, 0);
    const tvaAmount = ht * (tvaRate / 100);
    const ttc = ht + tvaAmount;

    totalTtcEl.textContent = ttc.toFixed(2);
    totalHtEl.textContent = ht.toFixed(2);
    tvaAmountEl.textContent = tvaAmount.toFixed(2);
}

tvaRateInput.addEventListener('input', computeInvoiceTotals);
remiseValueInput.addEventListener('input', computeInvoiceTotals);
remiseTypeInput.addEventListener('change', computeInvoiceTotals);
computeInvoiceTotals();
</script>
@endsection
