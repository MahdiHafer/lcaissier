@extends('layouts.app')

@section('content')
<style>
:root {
    --bg: #f3f6f4;
    --surface: #ffffff;
    --line: #dde5e0;
    --text: #142119;
    --muted: #67796d;
    --brand: #0f9d64;
    --brand-2: #0a7a4d;
    --danger: #dc3545;
}

body {
    background:
        radial-gradient(900px 300px at 8% -15%, #e3f8ec 0%, transparent 70%),
        radial-gradient(900px 300px at 98% 0%, #edf6ff 0%, transparent 60%),
        var(--bg);
}

.pos-shell {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 420px;
    gap: 16px;
}

.pos-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 20px;
    box-shadow: 0 16px 36px rgba(20, 33, 25, .08);
}

.catalog-header {
    position: sticky;
    top: 0;
    z-index: 5;
    padding: 14px;
    border-bottom: 1px solid var(--line);
    backdrop-filter: blur(8px);
    background: rgba(255, 255, 255, .88);
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
}

.scan-input {
    border: 1px solid var(--line);
    border-radius: 999px;
    padding: 12px 18px;
    font-size: 15px;
    color: var(--text);
}

.scan-input:focus {
    border-color: #88d4b4;
    box-shadow: 0 0 0 3px rgba(15, 157, 100, .15);
}

.chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.chip {
    border: 1px solid var(--line);
    background: #fff;
    color: var(--muted);
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
    transition: .2s ease;
}

.chip:hover {
    border-color: #b8cbc0;
    color: var(--text);
}

.chip.active {
    background: var(--brand);
    border-color: var(--brand);
    color: #fff;
}

.catalog-grid {
    max-height: calc(100vh - 230px);
    overflow: auto;
    padding: 14px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: 14px;
}

.product-card {
    border: 1px solid var(--line);
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    opacity: 0;
    transform: translateY(8px);
    animation: card-in .35s ease forwards;
    animation-delay: calc(var(--i, 0) * 18ms);
}

.product-card:hover {
    transform: translateY(-3px);
    border-color: #b6d7c6;
    box-shadow: 0 10px 26px rgba(16, 54, 36, .12);
}

.product-card:active {
    transform: scale(.985);
}

.product-image {
    height: 230px;
    background: linear-gradient(180deg, #eef3ef 0%, #f8faf9 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-meta {
    padding: 10px;
}

.product-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    line-height: 1.25;
    min-height: 34px;
}

.product-sub {
    font-size: 12px;
    color: var(--muted);
}

.product-bottom {
    margin-top: 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.product-price {
    font-size: 15px;
    font-weight: 800;
    color: var(--brand-2);
}

.tap-hint {
    font-size: 11px;
    color: #8a9a90;
}

.right-col {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.section-head {
    padding: 12px 14px;
    border-bottom: 1px solid var(--line);
    font-weight: 700;
    color: var(--text);
}

.cart-list {
    max-height: 45vh;
    overflow: auto;
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.cart-item {
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 10px;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 10px;
    background: #fff;
}

.cart-name {
    font-weight: 700;
    font-size: 14px;
    color: var(--text);
    margin-bottom: 2px;
}

.cart-meta {
    font-size: 12px;
    color: var(--muted);
}

.qty-toggle {
    border: 1px solid var(--line);
    border-radius: 999px;
    background: #fff;
    font-size: 12px;
    padding: 4px 10px;
    color: var(--text);
}

.qty-toggle.return {
    background: #fff3cd;
    border-color: #ffe08a;
}

.cart-right {
    text-align: right;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: flex-end;
}

.cart-price {
    font-weight: 800;
    color: var(--brand-2);
    font-size: 14px;
}

.remove-btn {
    border: 1px solid #f2c2c8;
    background: #fff;
    color: var(--danger);
    border-radius: 999px;
    padding: 2px 8px;
    font-size: 12px;
}

.summary {
    padding: 15px;
}

.total-wrap {
    border: 1px dashed var(--line);
    border-radius: 14px;
    padding: 10px;
    margin-bottom: 12px;
}

.total-value {
    font-size: 30px;
    font-weight: 800;
    color: var(--brand-2);
    line-height: 1;
}

.btn-pill {
    border-radius: 999px;
}

.toast-pos {
    position: fixed;
    right: 20px;
    bottom: 20px;
    background: #13251c;
    color: #fff;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 13px;
    opacity: 0;
    transform: translateY(10px);
    transition: .24s ease;
    z-index: 3000;
}

.toast-pos.show {
    opacity: 1;
    transform: translateY(0);
}

.variant-overlay {
    position: fixed;
    inset: 0;
    background: rgba(10, 20, 15, .45);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 3500;
    padding: 18px;
}

.variant-overlay.show {
    display: flex;
}

.variant-modal {
    width: min(640px, 96vw);
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--line);
    box-shadow: 0 22px 48px rgba(8, 20, 14, .25);
    overflow: hidden;
}

.variant-header {
    padding: 14px 16px;
    border-bottom: 1px solid var(--line);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.variant-body {
    padding: 14px;
    max-height: 58vh;
    overflow: auto;
}

.variant-item {
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 10px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    cursor: pointer;
    transition: .2s ease;
}

.variant-item:hover {
    border-color: #93d2b4;
    background: #f7fcf9;
}

.variant-item.disabled {
    cursor: not-allowed;
    opacity: .45;
    background: #fafafa;
}

.variant-stock {
    font-size: 12px;
    font-weight: 700;
    color: #0a7a4d;
}

.variant-color {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.variant-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 1px solid rgba(0, 0, 0, .18);
    flex: 0 0 14px;
}

@keyframes card-in {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 1160px) {
    .pos-shell {
        grid-template-columns: 1fr;
    }

    .catalog-grid {
        max-height: 52vh;
    }
}
</style>

<div class="container-fluid" id="posContainer">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">POS Caisse <span class="text-muted fs-6" id="itemCount">(0 articles)</span></h2>
        <div class="d-flex gap-2">
            <button id="fullscreenBtn" class="btn btn-outline-dark btn-pill">Plein ecran POS</button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-secondary btn-pill">Deconnexion</button>
            </form>
        </div>
    </div>

    <div class="pos-shell">
        <section class="pos-card">
            <div class="catalog-header">
                <input type="text" id="code-scan" class="form-control scan-input" placeholder="Scanner code-barres ou rechercher un produit...">
                <div class="chips" id="categoryChips">
                    <button class="chip active" type="button" data-category="all">Tout</button>
                    @foreach($categories as $category)
                        <button class="chip" type="button" data-category="{{ $category->id }}">{{ $category->name }}</button>
                    @endforeach
                </div>
            </div>

            <div class="catalog-grid" id="catalogGrid">
                @foreach($products as $idx => $product)
                    <article class="product-card"
                             style="--i:{{ $idx }}"
                             data-name="{{ strtolower($product->marque . ' ' . $product->modele . ' ' . ($product->codebar ?? '') . ' ' . ($product->reference ?? '')) }}"
                             data-category="{{ $product->category_id ?? 'none' }}"
                             data-id="{{ $product->id }}"
                             data-code="{{ $product->codebar }}">
                        <div class="product-image">
                            @if($product->image)
                                <img src="{{ asset($product->image) }}" alt="{{ $product->marque }}">
                            @else
                                <span class="text-muted small">Pas d'image</span>
                            @endif
                        </div>
                        <div class="product-meta">
                            <div class="product-title">{{ $product->marque }}</div>
                            <div class="product-sub">Ref: {{ $product->reference ?: '-' }}</div>
                            <div class="product-sub">{{ $product->category ? $product->category->name : ($product->categorie ?: 'Produit') }}</div>
                            <div class="product-bottom">
                                <span class="product-price">{{ number_format($product->prix_vente, 2) }} DH</span>
                                <span class="product-sub">Stock {{ $product->quantite }}</span>
                            </div>
                            <div class="tap-hint mt-1">Toucher pour ajouter</div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="right-col">
            <section class="pos-card">
                <div class="section-head d-flex justify-content-between align-items-center">
                    <span>Panier</span>
                    <button id="clearCartBtn" class="btn btn-sm btn-outline-secondary btn-pill">Vider</button>
                </div>

                <div class="cart-list" id="cartList"></div>
            </section>

            <section class="pos-card">
                <div class="summary">
                    <form method="POST" action="{{ route('caisse.valider') }}">
                        @csrf
                        <div class="total-wrap">
                            <div class="small text-muted">Total</div>
                            <div class="total-value" id="totalValue">0.00 DH</div>
                            <input type="hidden" name="total" id="totalInput" value="0">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small">Remise</label>
                            <div class="input-group">
                                <input type="text" name="remise" class="form-control" placeholder="0">
                                <select name="type_remise" class="form-select" style="max-width:90px">
                                    <option value="dh">DH</option>
                                    <option value="%">%</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Net a payer</div>
                            <div id="net-a-payer" class="fw-bold text-success fs-5">0.00 DH</div>
                            <input type="hidden" name="net_a_payer_calcule" id="net-a-payer-input" value="0">
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="clientSwitch">
                            <label class="form-check-label" for="clientSwitch">Vente client</label>
                        </div>

                        <div id="client-fields" style="display:none;">
                            <div class="mb-2"><input type="text" name="new_client_nom" class="form-control" placeholder="Nom client"></div>
                            <div class="mb-2"><input type="text" name="new_client_telephone" class="form-control" placeholder="Telephone client"></div>
                        </div>
                        <input type="hidden" name="comptoir" id="comptoir-input" value="1">

                        <div class="mb-3">
                            <label class="form-label small">Paiement</label>
                            <select name="mode_paiement" class="form-select" id="modePaiement" required>
                                <option value="Especes">Especes</option>
                                <option value="Virement">Virement</option>
                                <option value="Credit">Credit</option>
                                <option value="Cheque">Cheque</option>
                                <option value="TPE">TPE</option>
                                <option value="Avoir">Avoir</option>
                            </select>
                        </div>

                        <div class="mb-3" id="montant-paye-wrapper" style="display:none;">
                            <input type="number" step="0.01" name="montant_paye" class="form-control" placeholder="Montant paye">
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('caisse.imprimer') }}" target="_blank" class="btn btn-outline-secondary btn-pill" id="print-btn">Imprimer</a>
                            <button type="submit" class="btn btn-success btn-pill">Valider la vente</button>
                        </div>
                    </form>
                </div>
            </section>
        </aside>
    </div>
</div>

<div id="toastPos" class="toast-pos"></div>
<div id="variantOverlay" class="variant-overlay" aria-hidden="true">
    <div class="variant-modal">
        <div class="variant-header">
            <div>
                <div class="fw-bold" id="variantProductTitle">Choisir une variante</div>
                <div class="small text-muted" id="variantProductSubtitle">Selectionnez la taille/couleur a vendre</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="closeVariantModal">Fermer</button>
        </div>
        <div class="variant-body" id="variantList"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const csrfToken = "{{ csrf_token() }}";
const addUrl = "{{ route('caisse.add') }}";
const clearUrl = "{{ route('caisse.vider') }}";
const toggleBase = "{{ url('/caisse/toggle-retour') }}";
const removeBase = "{{ url('/caisse/remove') }}";

const scanner = document.getElementById('code-scan');
const cards = Array.from(document.querySelectorAll('.product-card'));
const chips = Array.from(document.querySelectorAll('.chip'));
const cartList = document.getElementById('cartList');
const totalValue = document.getElementById('totalValue');
const totalInput = document.getElementById('totalInput');
const itemCount = document.getElementById('itemCount');
const toastPos = document.getElementById('toastPos');
const clearCartBtn = document.getElementById('clearCartBtn');
const variantOverlay = document.getElementById('variantOverlay');
const variantList = document.getElementById('variantList');
const variantProductTitle = document.getElementById('variantProductTitle');
const variantProductSubtitle = document.getElementById('variantProductSubtitle');
const closeVariantModal = document.getElementById('closeVariantModal');

let cartState = @json(session('panier', []));
let pendingVariantPayload = null;
const companyName = @json($companySettings['name'] ?? config('app.name'));
const companyAddress = @json($companySettings['address'] ?? null);
const companyPhone = @json($companySettings['phone'] ?? null);
const companyEmail = @json($companySettings['email'] ?? null);
const companyIce = @json($companySettings['ice'] ?? null);

scanner.focus();

function money(n) {
    return `${Number(n).toFixed(2)} DH`;
}

function escapeHtml(str) {
    return (str || '').replace(/[&<>'"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[c]));
}

function showToast(message) {
    toastPos.textContent = message;
    toastPos.classList.add('show');
    window.clearTimeout(showToast._timer);
    showToast._timer = window.setTimeout(() => toastPos.classList.remove('show'), 1200);
}

function computeCartTotal() {
    let total = 0;
    let count = 0;
    Object.values(cartState).forEach((item) => {
        const qty = Number(item.quantite || 0);
        const signedQty = item.retour ? -qty : qty;
        total += Number(item.prix || 0) * signedQty;
        count += Math.abs(qty);
    });
    return { total, count };
}

function updateNetAPayerFromTotal(total) {
    const remiseInput = document.querySelector('input[name="remise"]');
    const typeRemise = document.querySelector('select[name="type_remise"]');
    const netDisplay = document.getElementById('net-a-payer');
    const netHiddenInput = document.getElementById('net-a-payer-input');

    let remise = parseFloat(remiseInput.value) || 0;
    let net = total;

    if (typeRemise.value === '%') {
        remise = total * (remise / 100);
    }

    net = Math.max(total - remise, 0);
    netDisplay.textContent = money(net);
    netHiddenInput.value = net.toFixed(2);
}

function renderCart() {
    const keys = Object.keys(cartState);

    if (!keys.length) {
        cartList.innerHTML = '<div class="text-center text-muted py-4">Panier vide</div>';
    } else {
        cartList.innerHTML = keys.map((key) => {
            const item = cartState[key];
            const qty = Number(item.quantite || 0);
            const signedQty = item.retour ? -qty : qty;
            const lineTotal = Number(item.prix || 0) * signedQty;

            return `
                <div class="cart-item">
                    <div>
                        <div class="cart-name">${escapeHtml(item.nom)}</div>
                        <div class="cart-meta">Ref: ${escapeHtml(item.reference || '-')}</div>
                        <div class="cart-meta">${escapeHtml(item.codebar || '-')}</div>
                        ${item.variant_label ? `<div class="cart-meta">${escapeHtml(item.variant_label)}</div>` : ''}
                        <button type="button" class="qty-toggle ${item.retour ? 'return' : ''}" data-action="toggle" data-key="${encodeURIComponent(key)}">
                            Qte ${item.retour ? '-' + qty : qty}
                        </button>
                    </div>
                    <div class="cart-right">
                        <div class="cart-price">${money(lineTotal)}</div>
                        <button type="button" class="remove-btn" data-action="remove" data-key="${encodeURIComponent(key)}">Retirer</button>
                    </div>
                </div>
            `;
        }).join('');
    }

    const { total, count } = computeCartTotal();
    totalValue.textContent = money(total);
    totalInput.value = total.toFixed(2);
    itemCount.textContent = `(${count} articles)`;
    updateNetAPayerFromTotal(total);
}

async function postJson(url, payload, method = 'POST') {
    const body = payload instanceof URLSearchParams ? payload : JSON.stringify(payload || {});
    const isForm = body instanceof URLSearchParams;

    const res = await fetch(url, {
        method,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            ...(isForm ? { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' } : { 'Content-Type': 'application/json' })
        },
        body
    });

    const data = await res.json();
    if (!res.ok) {
        if (res.status === 409 && data.needs_variant) {
            return data;
        }
        throw new Error(data.error || 'Erreur serveur');
    }
    return data;
}

async function addToCart(payload) {
    const data = await postJson(addUrl, payload);
    if (data.needs_variant) {
        openVariantModal(data, payload);
        return;
    }
    cartState = data.panier || {};
    renderCart();
    showToast((data.added_name || 'Produit') + ' ajoute au panier');
}

cards.forEach((card) => {
    card.addEventListener('click', async () => {
        try {
            const code = card.dataset.code;
            const productId = card.dataset.id;
            await addToCart(code ? { code } : { product_id: productId });
        } catch (e) {
            showToast(e.message);
        }
    });
});

scanner.addEventListener('keypress', async function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const value = this.value.trim();
        if (!value) return;

        try {
            await addToCart({ code: value });
            scanner.value = '';
            filterCatalog();
        } catch (e2) {
            filterCatalog();
            showToast('Produit non trouve');
        }
    }
});

function filterCatalog() {
    const term = scanner.value.trim().toLowerCase();
    const activeChip = document.querySelector('.chip.active');
    const selected = activeChip ? activeChip.dataset.category : 'all';

    cards.forEach(card => {
        const matchesText = term === '' || card.dataset.name.includes(term);
        const matchesCategory = selected === 'all' || card.dataset.category === selected;
        card.style.display = (matchesText && matchesCategory) ? '' : 'none';
    });
}

function openVariantModal(data, payload) {
    pendingVariantPayload = payload;
    variantProductTitle.textContent = data.product?.name || 'Choisir une variante';
    variantProductSubtitle.textContent = `Ref: ${data.product?.reference || '-'} | Code: ${data.product?.codebar || '-'}`;

    const variants = Array.isArray(data.variants) ? data.variants : [];
    if (!variants.length) {
        showToast('Aucune variante disponible');
        return;
    }

    variantList.innerHTML = variants.map((variant) => {
        const disabled = Number(variant.quantity || 0) <= 0;
        const colorHex = variant.color_hex && /^#[0-9A-Fa-f]{6}$/.test(variant.color_hex) ? variant.color_hex : '#ffffff';
        return `
            <div class="variant-item ${disabled ? 'disabled' : ''}" data-variant-id="${variant.id}" data-disabled="${disabled ? 1 : 0}">
                <div>
                    <div class="fw-semibold">${escapeHtml(variant.label || 'Variante')}</div>
                    <div class="small text-muted">Taille: ${escapeHtml(variant.size || '-')} | Couleur: <span class="variant-color"><span class="variant-dot" style="background:${colorHex};"></span>${escapeHtml(variant.color || '-')}</span></div>
                </div>
                <div class="variant-stock">${variant.quantity} en stock</div>
            </div>
        `;
    }).join('');

    variantOverlay.classList.add('show');
    variantOverlay.setAttribute('aria-hidden', 'false');
}

function closeVariantPopup() {
    variantOverlay.classList.remove('show');
    variantOverlay.setAttribute('aria-hidden', 'true');
    pendingVariantPayload = null;
}

scanner.addEventListener('input', filterCatalog);

chips.forEach(chip => {
    chip.addEventListener('click', () => {
        chips.forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        filterCatalog();
    });
});

variantList.addEventListener('click', async (e) => {
    const row = e.target.closest('.variant-item');
    if (!row) return;
    if (row.dataset.disabled === '1') return;

    const variantId = row.dataset.variantId;
    if (!variantId || !pendingVariantPayload) return;

    const payload = Object.assign({}, pendingVariantPayload, { variant_id: Number(variantId) });
    try {
        await addToCart(payload);
        closeVariantPopup();
    } catch (err) {
        showToast(err.message);
    }
});

closeVariantModal.addEventListener('click', closeVariantPopup);
variantOverlay.addEventListener('click', (e) => {
    if (e.target === variantOverlay) {
        closeVariantPopup();
    }
});

cartList.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;

    const action = btn.dataset.action;
    const key = decodeURIComponent(btn.dataset.key || '');

    try {
        if (action === 'toggle') {
            const data = await postJson(`${toggleBase}/${encodeURIComponent(key)}`, {});
            cartState = data.panier || {};
            renderCart();
        }

        if (action === 'remove') {
            const form = new URLSearchParams();
            form.append('_method', 'DELETE');
            const data = await postJson(`${removeBase}/${encodeURIComponent(key)}`, form);
            cartState = data.panier || {};
            renderCart();
            showToast('Article retire');
        }
    } catch (err) {
        showToast(err.message);
    }
});

clearCartBtn.addEventListener('click', async () => {
    try {
        const data = await postJson(clearUrl, {});
        cartState = data.panier || {};
        renderCart();
        showToast('Panier vide');
    } catch (err) {
        showToast(err.message);
    }
});

const remiseInput = document.querySelector('input[name="remise"]');
const typeRemise = document.querySelector('select[name="type_remise"]');
remiseInput.addEventListener('input', () => updateNetAPayerFromTotal(computeCartTotal().total));
typeRemise.addEventListener('change', () => updateNetAPayerFromTotal(computeCartTotal().total));

document.getElementById('modePaiement').addEventListener('change', function () {
    document.getElementById('montant-paye-wrapper').style.display = this.value === 'Credit' ? 'block' : 'none';
});

document.getElementById('clientSwitch').addEventListener('change', function () {
    const fields = document.getElementById('client-fields');
    const comptoir = document.getElementById('comptoir-input');
    fields.style.display = this.checked ? 'block' : 'none';
    comptoir.value = this.checked ? 0 : 1;
});

function getTicketInfosPayload() {
    const nom = document.querySelector('input[name="new_client_nom"]').value;
    const telephone = document.querySelector('input[name="new_client_telephone"]').value;
    const mode = document.getElementById('modePaiement').value;
    const remise = document.querySelector('input[name="remise"]').value;
    const typeRemiseValue = document.querySelector('select[name="type_remise"]').value;
    const netAPayer = document.querySelector('#net-a-payer-input').value;

    return {
        nom: nom,
        telephone: telephone,
        mode: mode,
        remise: remise,
        type_remise: typeRemiseValue,
        net_a_payer: netAPayer
    };
}

function storeTicketInfos() {
    return fetch("{{ route('caisse.storeInfosTicket') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(getTicketInfosPayload())
    }).then(res => res.json());
}

document.getElementById('print-btn').addEventListener('click', function (e) {
    e.preventDefault();
    printDirectThermal();
});

function sanitizeText(text) {
    return (text || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^\x20-\x7E\n]/g, ' ')
        .trim();
}

function leftRight(left, right, width = 42) {
    const l = sanitizeText(left);
    const r = sanitizeText(right);
    if ((l.length + r.length + 1) >= width) {
        return `${l}\n${' '.repeat(Math.max(0, width - r.length))}${r}`;
    }
    return l + ' '.repeat(width - l.length - r.length) + r;
}

function buildReceiptText() {
    const lines = [];
    const now = new Date();
    const payload = getTicketInfosPayload();
    const totals = computeCartTotal();
    const total = Number(totals.total || 0);
    const net = Number(payload.net_a_payer || total);

    lines.push(sanitizeText(companyName || 'Societe'));
    if (companyAddress) lines.push(sanitizeText(companyAddress));
    if (companyPhone || companyEmail) {
        lines.push(sanitizeText(`${companyPhone || '-'}${companyEmail ? ` | ${companyEmail}` : ''}`));
    }
    if (companyIce) lines.push(`ICE: ${sanitizeText(companyIce)}`);
    lines.push(now.toLocaleString('fr-FR'));
    lines.push('-'.repeat(42));

    if (payload.nom) lines.push(`Client: ${sanitizeText(payload.nom)}`);
    if (payload.telephone) lines.push(`Tel: ${sanitizeText(payload.telephone)}`);
    if (payload.nom || payload.telephone) lines.push('-'.repeat(42));

    Object.values(cartState).forEach((item) => {
        const name = sanitizeText(item.nom || 'Produit');
        const ref = sanitizeText(item.reference || '');
        const qty = Number(item.quantite || 0);
        const unit = Number(item.prix || 0);
        const lineTotal = qty * unit;
        lines.push(name);
        if (ref) lines.push(`Ref: ${ref}`);
        if (item.variant_label) lines.push(`Var: ${sanitizeText(item.variant_label)}`);
        lines.push(leftRight(`${qty} x ${unit.toFixed(2)}`, `${lineTotal.toFixed(2)} DH`));
        lines.push('');
    });

    lines.push('-'.repeat(42));
    lines.push(leftRight('TOTAL', `${total.toFixed(2)} DH`));
    if (Number(payload.remise || 0) > 0) {
        const remiseLabel = payload.type_remise === '%' ? `${payload.remise}%` : `${Number(payload.remise).toFixed(2)} DH`;
        lines.push(leftRight('Remise', remiseLabel));
    }
    lines.push(leftRight('NET', `${net.toFixed(2)} DH`));
    if (payload.mode) lines.push(leftRight('Paiement', sanitizeText(payload.mode)));
    lines.push('-'.repeat(42));
    lines.push('Merci pour votre visite');
    lines.push('\n\n\n');
    return lines.join('\n');
}

function bytesFromText(text) {
    return new TextEncoder().encode(text);
}

function concatBytes(...arrays) {
    const total = arrays.reduce((sum, arr) => sum + arr.length, 0);
    const merged = new Uint8Array(total);
    let offset = 0;
    arrays.forEach((arr) => {
        merged.set(arr, offset);
        offset += arr.length;
    });
    return merged;
}

function toBase64(bytes) {
    let binary = '';
    const chunk = 0x8000;
    for (let i = 0; i < bytes.length; i += chunk) {
        binary += String.fromCharCode(...bytes.subarray(i, i + chunk));
    }
    return btoa(binary);
}

function buildEscPosPayload() {
    const init = new Uint8Array([0x1B, 0x40]);
    const alignCenter = new Uint8Array([0x1B, 0x61, 0x01]);
    const alignLeft = new Uint8Array([0x1B, 0x61, 0x00]);
    const cut = new Uint8Array([0x1D, 0x56, 0x42, 0x00]);
    const body = bytesFromText(buildReceiptText());
    return concatBytes(init, alignCenter, alignLeft, body, cut);
}

function isAndroid() {
    return /Android/i.test(navigator.userAgent || '');
}

function printViaRawBT(bytes) {
    const b64 = toBase64(bytes);
    window.location.href = `rawbt:base64,${encodeURIComponent(b64)}`;
}

async function ensureQzLoaded() {
    if (window.qz) return;
    await new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qz-tray/2.2.4/qz-tray.js';
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

async function printViaQz(bytes) {
    await ensureQzLoaded();
    if (!window.qz) throw new Error('QZ Tray indisponible');

    qz.security.setCertificatePromise((resolve) => resolve(null));
    qz.security.setSignaturePromise(() => (resolve) => resolve());

    if (!qz.websocket.isActive()) {
        await qz.websocket.connect();
    }

    let printerName = null;
    try {
        printerName = await qz.printers.getDefault();
    } catch (e) {
        printerName = null;
    }

    const config = qz.configs.create(printerName || null);
    const data = [{
        type: 'raw',
        format: 'command',
        flavor: 'base64',
        data: toBase64(bytes)
    }];

    await qz.print(config, data);
}

async function printDirectThermal() {
    if (!Object.keys(cartState).length) {
        showToast('Panier vide');
        return;
    }

    try {
        const data = await storeTicketInfos();
        if (!data || !data.success) {
            showToast('Impossible de preparer le ticket');
            return;
        }
    } catch (e) {
        showToast('Erreur sauvegarde infos ticket');
        return;
    }
    try {
        if (window.AndroidPrinter) {
            const bytes = buildEscPosPayload();
            const payloadB64 = toBase64(bytes);

            if (typeof window.AndroidPrinter.printEscPosStatus === 'function') {
                const status = String(window.AndroidPrinter.printEscPosStatus(payloadB64) || '');
                if (status.startsWith('OK|')) {
                    showToast(status.slice(3) || 'Ticket envoye');
                } else if (status.startsWith('ERR|')) {
                    showToast(status.slice(4) || 'Erreur impression');
                } else {
                    showToast(status || 'Erreur impression');
                }
                return;
            }

            if (typeof window.AndroidPrinter.printEscPos === 'function') {
                const printed = window.AndroidPrinter.printEscPos(payloadB64);
                showToast(printed ? 'Ticket envoye a l imprimante' : 'Imprimante non connectee');
                return;
            }
        }
    } catch (e) {
        showToast('Erreur impression Android');
    }

    window.open("{{ route('caisse.imprimer') }}", "_blank");
}

const fullscreenBtn = document.getElementById('fullscreenBtn');
fullscreenBtn.addEventListener('click', async () => {
    try {
        if (!document.fullscreenElement) {
            await document.documentElement.requestFullscreen();
            fullscreenBtn.textContent = 'Quitter plein ecran';
            showToast('Mode plein ecran active');
        } else {
            await document.exitFullscreen();
            fullscreenBtn.textContent = 'Plein ecran POS';
            showToast('Mode plein ecran quitte');
        }
    } catch (err) {
        showToast('Plein ecran non supporte');
    }
});

document.addEventListener('fullscreenchange', () => {
    fullscreenBtn.textContent = document.fullscreenElement ? 'Quitter plein ecran' : 'Plein ecran POS';
});

renderCart();
</script>
@endsection

