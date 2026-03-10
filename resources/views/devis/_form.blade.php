@php
    $isEdit = isset($devis);
    $action = $isEdit ? route('devis.update', $devis) : route('devis.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<form method="POST" action="{{ $action }}" class="card p-4">
    @csrf
    @if($isEdit)
        @method($method)
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label">Date du devis</label>
            <input type="date" name="date_devis" class="form-control" value="{{ old('date_devis', $isEdit ? $devis->date_devis : now()->toDateString()) }}" required>
        </div>
        <div class="col-md-5">
            <label class="form-label">Client</label>
            <select name="client_id" class="form-select">
                <option value="">Client comptoir</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ (string)old('client_id', $isEdit ? $devis->client_id : '') === (string)$client->id ? 'selected' : '' }}>
                        {{ $client->nom }}{{ $client->telephone ? ' - '.$client->telephone : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Numero</label>
            <input type="text" class="form-control" value="{{ $isEdit ? $devis->numero : 'Auto generation' }}" disabled>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $isEdit ? $devis->notes : '') }}</textarea>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="m-0">Lignes du devis</h5>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="addLineBtn">Ajouter ligne</button>
    </div>

    <div class="table-responsive border rounded-3">
        <table class="table align-middle mb-0" id="devis-lines-table">
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
            <tbody id="devis-lines-body"></tbody>
        </table>
    </div>

    <div class="text-end mt-3">
        <strong>Total Devis: <span id="devis-grand-total">0.00</span> DH</strong>
    </div>

    <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Enregistrer modifications' : 'Creer devis' }}</button>
    </div>
</form>

@push('scripts')
@php
    $productsCatalogData = $products->map(function ($p) {
        $designation = trim($p->marque . ' ' . $p->modele);
        $codebar = trim((string) ($p->codebar ?? ''));
        $reference = trim((string) ($p->reference ?? ''));
        return [
            'id' => $p->id,
            'designation' => $designation,
            'codebar' => $codebar,
            'reference' => $reference,
            'display' => trim($designation . ($reference !== '' ? ' | REF: ' . $reference : '') . ($codebar !== '' ? ' | CB: ' . $codebar : '')),
            'image' => $p->image ? asset($p->image) : '',
            'image_path' => $p->image ?: '',
            'prix' => (float) ($p->prix_vente_ttc ?? $p->prix_vente),
            'search' => strtolower(trim($designation . ' ' . $reference . ' ' . $codebar)),
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
    } elseif (isset($devis)) {
        $existingLinesData = $devis->details->map(function ($d) {
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

const tbody = document.getElementById('devis-lines-body');
const grandTotalEl = document.getElementById('devis-grand-total');

function productDatalistOptionsHtml() {
    const options = [];
    productsCatalog.forEach((p) => {
        options.push(`<option value="${p.display}" data-id="${p.id}"></option>`);
    });
    return options.join('');
}

function findProductByQuery(query) {
    const q = String(query || '').trim().toLowerCase();
    if (!q) return null;

    let picked = productsCatalog.find((p) => p.display.toLowerCase() === q);
    if (picked) return picked;
    picked = productsCatalog.find((p) => String(p.codebar || '').toLowerCase() === q);
    if (picked) return picked;
    picked = productsCatalog.find((p) => String(p.reference || '').toLowerCase() === q);
    if (picked) return picked;
    return productsCatalog.find((p) => String(p.search || '').includes(q)) || null;
}

function createLine(line = {}) {
    const datalistId = `devis-products-list-${Date.now()}-${Math.floor(Math.random() * 10000)}`;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <input type="hidden" name="product_id[]" class="line-product-id" value="${line.product_id || ''}">
            <input type="text" class="form-control line-product-search" list="${datalistId}" placeholder="Code-barres, reference ou designation">
            <datalist id="${datalistId}">${productDatalistOptionsHtml()}</datalist>
        </td>
        <td>
            <input type="hidden" name="image[]" class="line-image-path" value="${line.image_path || ''}">
            <img class="line-image-preview" src="${line.image || ''}" alt="" style="width:52px;height:52px;border-radius:8px;object-fit:cover;border:1px solid #ddd;${line.image ? '' : 'display:none;'}">
            <span class="line-image-empty text-muted small" style="${line.image ? 'display:none;' : ''}">Aucune</span>
        </td>
        <td><input type="text" name="designation[]" class="form-control line-designation" value="${line.designation || ''}" required></td>
        <td><input type="number" name="quantite[]" class="form-control line-qty" min="1" value="${line.quantite || 1}" required></td>
        <td><input type="number" step="0.01" name="prix_unitaire[]" class="form-control line-price" min="0" value="${line.prix_unitaire || 0}" required></td>
        <td><input type="text" class="form-control line-total" value="0.00" readonly></td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger line-remove">X</button></td>
    `;
    tbody.appendChild(tr);

    const productIdInput = tr.querySelector('.line-product-id');
    const productSearchInput = tr.querySelector('.line-product-search');
    const designationInput = tr.querySelector('.line-designation');
    const priceInput = tr.querySelector('.line-price');
    const qtyInput = tr.querySelector('.line-qty');
    const imagePreview = tr.querySelector('.line-image-preview');
    const imageEmpty = tr.querySelector('.line-image-empty');
    const imagePathInput = tr.querySelector('.line-image-path');
    const lineTotalInput = tr.querySelector('.line-total');

    function applyPickedProduct(picked) {
        if (!picked) return;
        productIdInput.value = picked.id;
        productSearchInput.value = picked.display;

        designationInput.value = picked.designation || picked.display;
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
    }

    function resolveProductFromInput() {
        const picked = findProductByQuery(productSearchInput.value);
        if (picked) {
            applyPickedProduct(picked);
        }
    }

    productSearchInput.addEventListener('change', resolveProductFromInput);
    productSearchInput.addEventListener('blur', resolveProductFromInput);
    productSearchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            resolveProductFromInput();
        }
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

    if (line.product_id) {
        const picked = productsCatalog.find((p) => String(p.id) === String(line.product_id));
        if (picked) {
            productSearchInput.value = picked.display;
        }
    }

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
