@php
    $isEdit = isset($bon);
    $action = $isEdit ? route('bons-livraison.update', $bon) : route('bons-livraison.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<form method="POST" action="{{ $action }}" class="card p-4">
    @csrf
    @if($isEdit)
        @method($method)
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label">Date du bon</label>
            <input type="date" name="date_bon" class="form-control" value="{{ old('date_bon', $isEdit ? $bon->date_bon : now()->toDateString()) }}" required>
        </div>
        <div class="col-md-5">
            <label class="form-label">Client</label>
            <select name="client_id" class="form-select">
                <option value="">Client comptoir</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ (string)old('client_id', $isEdit ? $bon->client_id : '') === (string)$client->id ? 'selected' : '' }}>
                        {{ $client->nom }}{{ $client->telephone ? ' - '.$client->telephone : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Numero</label>
            <input type="text" class="form-control" value="{{ $isEdit ? $bon->numero : 'Auto generation' }}" disabled>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $isEdit ? $bon->notes : '') }}</textarea>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="m-0">Lignes du bon</h5>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="addLineBtn">Ajouter ligne</button>
    </div>

    <div class="table-responsive border rounded-3">
        <table class="table align-middle mb-0" id="bl-lines-table">
            <thead>
            <tr>
                <th style="min-width:220px">Produit</th>
                <th style="min-width:110px">Image</th>
                <th style="min-width:220px">Designation</th>
                <th style="min-width:90px">Qte</th>
                <th style="min-width:120px">Prix</th>
                <th style="min-width:130px">Total</th>
                <th></th>
            </tr>
            </thead>
            <tbody id="bl-lines-body"></tbody>
        </table>
    </div>

    <div class="text-end mt-3">
        <strong>Total BL: <span id="bl-grand-total">0.00</span> DH</strong>
    </div>

    <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Enregistrer modifications' : 'Creer bon de livraison' }}</button>
    </div>
</form>

@push('scripts')
@php
    $productsCatalogData = $products->map(function ($p) {
        return [
            'id' => $p->id,
            'label' => trim($p->marque . ' ' . $p->modele . ($p->codebar ? ' [' . $p->codebar . ']' : '')),
            'designation' => trim($p->marque . ' ' . $p->modele),
            'image' => $p->image ? asset($p->image) : '',
            'image_path' => $p->image ?: '',
            'prix' => (float) $p->prix_vente,
        ];
    })->values();

    if (old('designation')) {
        $existingLinesData = collect(old('designation'))->map(function ($designation, $idx) {
            return [
                'product_id' => old('product_id')[$idx] ?? '',
                'image' => old('image')[$idx] ?? '',
                'image_path' => old('image')[$idx] ?? '',
                'designation' => $designation,
                'quantite' => old('quantite')[$idx] ?? 1,
                'prix_unitaire' => old('prix_unitaire')[$idx] ?? 0,
            ];
        })->values();
    } elseif (isset($bon)) {
        $existingLinesData = $bon->details->map(function ($d) {
            return [
                'product_id' => $d->product_id,
                'image' => $d->image ? asset($d->image) : '',
                'image_path' => $d->image,
                'designation' => $d->designation,
                'quantite' => $d->quantite,
                'prix_unitaire' => (float) $d->prix_unitaire,
            ];
        })->values();
    } else {
        $existingLinesData = collect();
    }
@endphp
<script>
const productsCatalog = @json($productsCatalogData);
const existingLines = @json($existingLinesData);

const tbody = document.getElementById('bl-lines-body');
const grandTotalEl = document.getElementById('bl-grand-total');

function productOptionsHtml(selectedId) {
    const options = ['<option value=\"\">Selectionner...</option>'];
    productsCatalog.forEach((p) => {
        options.push(`<option value=\"${p.id}\" ${String(selectedId) === String(p.id) ? 'selected' : ''}>${p.label}</option>`);
    });
    return options.join('');
}

function createLine(line = {}) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <select name=\"product_id[]\" class=\"form-select line-product\">${productOptionsHtml(line.product_id || '')}</select>
        </td>
        <td>
            <input type=\"hidden\" name=\"image[]\" class=\"line-image-path\" value=\"${line.image_path || ''}\">
            <img class=\"line-image-preview\" src=\"${line.image || ''}\" alt=\"\" style=\"width:52px;height:52px;border-radius:8px;object-fit:cover;border:1px solid #ddd;${line.image ? '' : 'display:none;'}\">
            <span class=\"line-image-empty text-muted small\" style=\"${line.image ? 'display:none;' : ''}\">Aucune</span>
        </td>
        <td><input type=\"text\" name=\"designation[]\" class=\"form-control line-designation\" value=\"${line.designation || ''}\" required></td>
        <td><input type=\"number\" name=\"quantite[]\" class=\"form-control line-qty\" min=\"1\" value=\"${line.quantite || 1}\" required></td>
        <td><input type=\"number\" step=\"0.01\" name=\"prix_unitaire[]\" class=\"form-control line-price\" min=\"0\" value=\"${line.prix_unitaire || 0}\" required></td>
        <td><input type=\"text\" class=\"form-control line-total\" value=\"0.00\" readonly></td>
        <td class=\"text-end\"><button type=\"button\" class=\"btn btn-sm btn-outline-danger line-remove\">X</button></td>
    `;
    tbody.appendChild(tr);

    const productSelect = tr.querySelector('.line-product');
    const designationInput = tr.querySelector('.line-designation');
    const priceInput = tr.querySelector('.line-price');
    const qtyInput = tr.querySelector('.line-qty');
    const imagePreview = tr.querySelector('.line-image-preview');
    const imageEmpty = tr.querySelector('.line-image-empty');
    const imagePathInput = tr.querySelector('.line-image-path');
    const lineTotalInput = tr.querySelector('.line-total');

    productSelect.addEventListener('change', () => {
        const picked = productsCatalog.find((p) => String(p.id) === String(productSelect.value));
        if (!picked) return;

        designationInput.value = picked.designation || picked.label;
        if (!priceInput.value || Number(priceInput.value) === 0) {
            priceInput.value = Number(picked.prix || 0).toFixed(2);
        }
        imagePathInput.value = picked.image_path || '';
        if (picked.image) {
            imagePreview.src = picked.image;
            imagePreview.style.display = '';
            imageEmpty.style.display = 'none';
        } else {
            imagePreview.style.display = 'none';
            imageEmpty.style.display = '';
        }
        recalcLine(tr);
    });

    function recalcLine(currentTr) {
        const qty = Number(currentTr.querySelector('.line-qty').value || 0);
        const price = Number(currentTr.querySelector('.line-price').value || 0);
        const lineTotal = qty * price;
        lineTotalInput.value = lineTotal.toFixed(2);
        recalcGrandTotal();
    }

    qtyInput.addEventListener('input', () => recalcLine(tr));
    priceInput.addEventListener('input', () => recalcLine(tr));

    tr.querySelector('.line-remove').addEventListener('click', () => {
        tr.remove();
        recalcGrandTotal();
    });

    recalcLine(tr);
}

function recalcGrandTotal() {
    let total = 0;
    document.querySelectorAll('.line-total').forEach((input) => {
        total += Number(input.value || 0);
    });
    grandTotalEl.textContent = total.toFixed(2);
}

document.getElementById('addLineBtn').addEventListener('click', () => createLine());

if (existingLines && existingLines.length) {
    existingLines.forEach((line) => createLine(line));
} else {
    createLine();
}
</script>
@endpush
