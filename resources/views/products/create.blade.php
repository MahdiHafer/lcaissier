@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Ajouter un produit</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="card p-4">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Reference produit</label>
                <input type="text" name="reference" id="reference-input" class="form-control" value="{{ old('reference') }}" placeholder="Ex: COS02001TH">
                <small class="text-muted">Format: TYPE(3) + GROUPE(2) + NNN(3 global auto) + LIGNE(variable) + SAISON(1)</small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Generation automatique reference</label>
                <div class="row g-2">
                    <div class="col-3">
                        <input type="text" name="reference_type" id="reference-type" class="form-control" maxlength="3" placeholder="Type" value="{{ old('reference_type') }}">
                    </div>
                    <div class="col-3">
                        <input type="text" name="reference_group" id="reference-group" class="form-control" maxlength="2" placeholder="Groupe" value="{{ old('reference_group') }}">
                    </div>
                    <div class="col-2">
                        <input type="text" name="reference_line" id="reference-line" class="form-control" maxlength="20" placeholder="Ligne" value="{{ old('reference_line') }}">
                    </div>
                    <div class="col-2">
                        <input type="text" name="reference_season" id="reference-season" class="form-control" maxlength="1" placeholder="Saison" value="{{ old('reference_season') }}">
                    </div>
                    <div class="col-2 d-grid">
                        <button type="button" class="btn btn-outline-primary" id="generate-reference-btn">Generer</button>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Code-barres</label>
                <div class="input-group">
                    <input type="text" name="codebar" id="codebar-input" class="form-control" required>
                    <button type="button" class="btn btn-outline-primary" onclick="generateUniqueEAN13()">Generer</button>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Designation</label>
                <input type="text" name="marque" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Categorie</label>
                <select name="category_id" class="form-select">
                    <option value="">Produit (par defaut)</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="categorie" value="Produit">
            </div>

            <div class="col-md-6">
                <label class="form-label">Etat</label>
                <select name="etat" class="form-select" required>
                    <option value="Neuf">Neuf</option>
                    <option value="Occasion">Occasion</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">TVA %</label>
                <input type="number" step="0.01" min="0" max="100" name="tva_rate" id="tva-rate" class="form-control" value="{{ old('tva_rate', 20) }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Prix achat HT</label>
                <input type="number" step="0.01" min="0" name="prix_achat_ht" id="prix-achat-ht" class="form-control" value="{{ old('prix_achat_ht') }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Prix achat TTC</label>
                <input type="number" step="0.01" min="0" name="prix_achat_ttc" id="prix-achat-ttc" class="form-control" value="{{ old('prix_achat_ttc') }}" required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Prix vente HT</label>
                <input type="number" step="0.01" min="0" name="prix_vente_ht" id="prix-vente-ht" class="form-control" value="{{ old('prix_vente_ht') }}" required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Prix vente TTC</label>
                <input type="number" step="0.01" min="0" name="prix_vente_ttc" id="prix-vente-ttc" class="form-control" value="{{ old('prix_vente_ttc') }}" required>
                <small class="text-muted">Utilise en caisse</small>
            </div>

            <div class="col-md-4">
                <label class="form-label">Stock total</label>
                <input type="number" id="stock-input" name="quantite" class="form-control" value="0">
            </div>

            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="hasVariantsSwitch" name="has_variants" value="1">
                    <label class="form-check-label" for="hasVariantsSwitch">Ce produit a des variantes (taille/couleur)</label>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Image produit</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <div class="col-md-6">
                <label class="form-label">Fournisseur</label>
                <select name="fournisseur_id" class="form-select">
                    <option value="">Choisir...</option>
                    @foreach($fournisseurs as $f)
                        <option value="{{ $f->id }}">{{ $f->nom }}{{ $f->societe ? ' - '.$f->societe : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12" id="variant-section" style="display:none;">
                <label class="form-label">Variantes de stock (taille + couleur)</label>
                <div id="variants-wrapper" class="d-flex flex-column gap-2"></div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addVariantRow()">Ajouter une variante</button>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
const colorOptions = `@foreach($colors as $color)<option value="{{ $color->id }}">{{ $color->name }}</option>@endforeach`;
const referenceNextUrl = "{{ route('products.referenceNext') }}";

function addVariantRow(size = '', colorId = '', quantity = 0) {
    const wrapper = document.getElementById('variants-wrapper');
    const row = document.createElement('div');
    row.className = 'row g-2 align-items-center variant-row';
    row.innerHTML = `
        <div class="col-md-4">
            <input type="text" name="variant_size[]" class="form-control" placeholder="Taille (S, M, L, 42...)" value="${size}">
        </div>
        <div class="col-md-4">
            <select name="variant_color_id[]" class="form-select">
                <option value="">Couleur</option>
                ${colorOptions}
            </select>
        </div>
        <div class="col-md-3">
            <input type="number" min="0" name="variant_quantity[]" class="form-control variant-qty" value="${quantity}">
        </div>
        <div class="col-md-1 text-end">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.variant-row').remove(); refreshStock();">X</button>
        </div>
    `;

    wrapper.appendChild(row);
    if (colorId) {
        row.querySelector('select[name="variant_color_id[]"]').value = colorId;
    }

    row.querySelector('.variant-qty').addEventListener('input', refreshStock);
    refreshStock();
}

function refreshStock() {
    if (!document.getElementById('hasVariantsSwitch').checked) {
        return;
    }
    let total = 0;
    document.querySelectorAll('.variant-qty').forEach((input) => {
        total += parseInt(input.value || '0', 10);
    });
    document.getElementById('stock-input').value = total;
}

function toggleVariantsMode() {
    const enabled = document.getElementById('hasVariantsSwitch').checked;
    const section = document.getElementById('variant-section');
    const stockInput = document.getElementById('stock-input');

    section.style.display = enabled ? 'block' : 'none';
    stockInput.readOnly = enabled;

    if (enabled) {
        if (!document.querySelector('.variant-row')) {
            addVariantRow();
        }
        refreshStock();
    }
}

async function generateUniqueEAN13() {
    let isUnique = false;
    let code = '';

    while (!isUnique) {
        const base = Math.floor(Math.random() * 1e12).toString().padStart(12, '0');
        const checksum = calculateEAN13Checksum(base);
        code = base + checksum;

        const response = await fetch(`/check-codebar?codebar=${code}`);
        const data = await response.json();
        if (data.unique) {
            isUnique = true;
        }
    }

    document.getElementById('codebar-input').value = code;
}

function calculateEAN13Checksum(code) {
    let sum = 0;
    for (let i = 0; i < 12; i++) {
        const digit = parseInt(code[i], 10);
        sum += (i % 2 === 0) ? digit : digit * 3;
    }
    return (10 - (sum % 10)) % 10;
}

async function generateProductReference() {
    const type = (document.getElementById('reference-type').value || '').trim().toUpperCase();
    const group = (document.getElementById('reference-group').value || '').trim();
    const line = (document.getElementById('reference-line').value || '').trim().toUpperCase();
    const season = (document.getElementById('reference-season').value || '').trim().toUpperCase();

    if (type.length !== 3 || group.length !== 2 || line.length < 1 || season.length !== 1) {
        alert('Remplissez Type(3), Groupe(2), Ligne(variable), Saison(1).');
        return;
    }

    const params = new URLSearchParams({ type, group, line, season });
    const response = await fetch(`${referenceNextUrl}?${params.toString()}`);
    const data = await response.json();

    if (!response.ok || !data.reference) {
        alert('Impossible de generer la reference.');
        return;
    }

    document.getElementById('reference-input').value = data.reference;
}

document.getElementById('generate-reference-btn').addEventListener('click', generateProductReference);
document.getElementById('hasVariantsSwitch').addEventListener('change', toggleVariantsMode);
toggleVariantsMode();

function round2(n) {
    return Math.round((Number(n) + Number.EPSILON) * 100) / 100;
}

function getTvaFactor() {
    const rate = Number(document.getElementById('tva-rate').value || 0);
    return 1 + (rate / 100);
}

function bindHtTtc(htId, ttcId) {
    const htInput = document.getElementById(htId);
    const ttcInput = document.getElementById(ttcId);
    let lock = false;

    htInput?.addEventListener('input', () => {
        if (lock) return;
        lock = true;
        const factor = getTvaFactor();
        const ht = Number(htInput.value || 0);
        ttcInput.value = round2(ht * factor).toFixed(2);
        lock = false;
    });

    ttcInput?.addEventListener('input', () => {
        if (lock) return;
        lock = true;
        const factor = getTvaFactor();
        const ttc = Number(ttcInput.value || 0);
        htInput.value = round2(ttc / factor).toFixed(2);
        lock = false;
    });
}

document.getElementById('tva-rate')?.addEventListener('input', () => {
    const achatHt = document.getElementById('prix-achat-ht');
    const venteHt = document.getElementById('prix-vente-ht');
    if (achatHt?.value) achatHt.dispatchEvent(new Event('input'));
    if (venteHt?.value) venteHt.dispatchEvent(new Event('input'));
});

bindHtTtc('prix-achat-ht', 'prix-achat-ttc');
bindHtTtc('prix-vente-ht', 'prix-vente-ttc');
</script>
@endsection
