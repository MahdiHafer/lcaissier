<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Etiquette Produit</title>
    <style>
        @page { size: 65mm 30mm; margin: 0; }

        html, body {
            width: 65mm;
            height: 30mm;
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            overflow: hidden;
            color: #0f1720;
            background: #fff;
        }

        .label {
            width: 64mm;
            height: 29mm;
            margin: 0;
            box-sizing: border-box;
            border: .2mm solid #131313;
            border-radius: 1mm;
            padding: .9mm 1.2mm .7mm;
            display: flex;
            flex-direction: column;
            gap: .45mm;
        }

        .name {
            text-align: center;
            font-weight: 800;
            font-size: 10.2px;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: .15px;
        }

        .separator {
            border-top: .2mm dashed #323232;
            margin: .05mm 0 .3mm;
        }

        .ref {
            display: flex;
            align-items: baseline;
            gap: .7mm;
            font-size: 9.2px;
            line-height: 1.1;
        }

        .k {
            font-weight: 700;
            white-space: nowrap;
        }

        .v {
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }

        .line {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 1.1mm;
            font-size: 7px;
            line-height: 1.1;
        }

        .line > div {
            flex: 1;
            min-width: 0;
            display: flex;
            gap: .6mm;
            align-items: baseline;
        }

        .price {
            margin-top: .45mm;
            margin-bottom: .1mm;
            text-align: center;
            font-size: 12.8px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: .15px;
        }

        .barcode {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .barcode img {
            width: 60mm;
            height: 8.7mm;
            object-fit: contain;
        }

        .barcode-text {
            font-size: 6.1px;
            line-height: 1;
            margin-top: .1mm;
            letter-spacing: .2px;
            max-width: 60mm;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .actions {
            position: fixed;
            top: 6px;
            right: 6px;
            z-index: 100;
            display: flex;
            gap: 6px;
        }

        .btn {
            border: 1px solid #d2dce6;
            background: #fff;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            padding: 6px 10px;
            cursor: pointer;
        }

        @media print {
            .actions { display: none; }
            html, body {
                width: 65mm !important;
                height: 30mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }
            .label {
                position: fixed;
                top: .5mm;
                left: .5mm;
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
@php
    $selectedVariant = $variant ?? null;
    $sizes = $selectedVariant
        ? ($selectedVariant->size ?: '')
        : $product->variants->pluck('size')->filter()->unique()->values()->implode(', ');
    $colors = $selectedVariant
        ? (optional($selectedVariant->color)->name ?: '')
        : $product->variants->map(function ($productVariant) {
            return optional($productVariant->color)->name;
        })->filter()->unique()->values()->implode(', ');
@endphp

<div class="actions">
    <button class="btn" type="button" onclick="window.print()">Imprimer</button>
</div>

<div class="label" id="label-root">
    <div class="name">{{ $product->marque ?: '-' }}</div>
    <div class="separator"></div>

    <div class="ref">
        <span class="k">Reference :</span>
        <span class="v">{{ $product->reference ?: '-' }}</span>
    </div>

    <div class="line">
        <div><span class="k">Taille :</span><span class="v">{{ $sizes !== '' ? $sizes : '-' }}</span></div>
        <div style="justify-content:flex-end;"><span class="k">Couleur :</span><span class="v">{{ $colors !== '' ? $colors : '-' }}</span></div>
    </div>

    <div class="price">{{ number_format($product->prix_vente, 2) }} DH</div>

    <div class="barcode">
        <img src="{{ $barcodeSrc ?? '' }}" alt="Code-barres">
        <div class="barcode-text">{{ $barcodeCode ?: ($product->codebar ?: '-') }}</div>
    </div>
</div>

<script>
const AUTO_DIRECT_PRINT = @json($directPrint ?? true);

window.addEventListener('load', () => {
    if (AUTO_DIRECT_PRINT) {
        window.print();
    }
});
</script>
</body>
</html>
