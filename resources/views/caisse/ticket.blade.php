<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket de caisse</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 3mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            width: 80mm;
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.25;
            color: #111;
            background: #fff;
        }

        .ticket {
            width: 100%;
            padding: 2mm 1mm;
        }

        .center { text-align: center; }
        .small { font-size: 10px; color: #4b4b4b; }
        .muted { color: #6a6a6a; }
        .mono { font-family: "Consolas", "Courier New", monospace; }

        .logo {
            max-height: 44px;
            max-width: 58mm;
            object-fit: contain;
            margin-bottom: 3px;
        }

        .brand {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: .3px;
            margin-bottom: 2px;
        }

        .separator {
            border-top: 1px dashed #222;
            margin: 7px 0;
        }

        .meta-row,
        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .meta-row span:last-child,
        .total-row span:last-child {
            text-align: right;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }

        .items th {
            text-align: left;
            font-size: 10px;
            color: #555;
            font-weight: 700;
            border-bottom: 1px solid #d9d9d9;
            padding: 0 0 3px 0;
        }

        .items th:last-child,
        .items td:last-child {
            text-align: right;
        }

        .items td {
            vertical-align: top;
            padding: 4px 0;
            border-bottom: 1px dotted #e5e5e5;
        }

        .item-name {
            font-weight: 700;
            color: #141414;
        }

        .item-sub {
            display: block;
            margin-top: 1px;
            font-size: 10px;
            color: #6a6a6a;
        }

        .totals {
            border: 1px solid #d9d9d9;
            border-radius: 6px;
            padding: 6px 7px;
            margin-top: 3px;
        }

        .total-row {
            margin: 2px 0;
        }

        .grand-total {
            margin-top: 3px;
            padding-top: 4px;
            border-top: 1px dashed #333;
            font-size: 13px;
            font-weight: 800;
            color: #000;
        }

        .footer-note {
            margin-top: 6px;
            font-size: 10px;
            color: #555;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body onload="window.print();">
@php
    $remise = $client['remise'] ?? null;
    $typeRemise = $client['type_remise'] ?? null;
    $net = $client['net_a_payer'] ?? $total;
    $companyName = $companySettings['name'] ?? config('app.name', 'Societe');
    $companyAddress = $companySettings['address'] ?? null;
    $companyPhone = $companySettings['phone'] ?? null;
    $companyEmail = $companySettings['email'] ?? null;
    $companyIce = $companySettings['ice'] ?? null;
@endphp

<div class="ticket">
    <div class="center">
        <div class="brand">{{ $companyName }}</div>
        @if(!empty($companyAddress))
            <div class="small">{{ $companyAddress }}</div>
        @endif
        @if(!empty($companyPhone) || !empty($companyEmail))
            <div class="small mono">
                {{ $companyPhone ?: '-' }}@if(!empty($companyEmail)) | {{ $companyEmail }}@endif
            </div>
        @endif
        @if(!empty($companyIce))
            <div class="small mono">ICE: {{ $companyIce }}</div>
        @endif
        <div class="small mono">{{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="separator"></div>

    @if (!empty($client['nom']) || !empty($client['telephone']))
    <div class="small">
        @if (!empty($client['nom']))
            <div class="meta-row"><span>Client</span><span>{{ $client['nom'] }}</span></div>
        @endif
        @if (!empty($client['telephone']))
            <div class="meta-row"><span>T&eacute;l</span><span class="mono">{{ $client['telephone'] }}</span></div>
        @endif
    </div>
    <div class="separator"></div>
    @endif

    <table class="items">
        <thead>
            <tr>
                <th>Article</th>
                <th class="mono">Montant</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($panier as $item)
            <tr>
                <td>
                    <span class="item-name">{{ $item['nom'] }}</span>
                    <span class="item-sub mono">{{ $item['quantite'] }} x {{ number_format($item['prix'], 2) }} DH</span>
                </td>
                <td class="mono">{{ number_format($item['prix'] * $item['quantite'], 2) }} DH</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="separator"></div>

    <div class="totals">
        <div class="total-row">
            <span>Total brut</span>
            <span class="mono">{{ number_format($total, 2) }} DH</span>
        </div>

        @if ($remise)
        <div class="total-row">
            <span>Remise</span>
            <span class="mono">
                @if ($typeRemise === '%')
                    {{ rtrim(rtrim(number_format((float) $remise, 2, '.', ''), '0'), '.') }} %
                @else
                    {{ number_format($remise, 2) }} DH
                @endif
            </span>
        </div>
        @endif

        <div class="total-row grand-total">
            <span>Net &agrave; payer</span>
            <span class="mono">{{ number_format($net, 2) }} DH</span>
        </div>

        @if (!empty($mode_paiement))
        <div class="total-row small">
            <span>Paiement</span>
            <span>{{ ucfirst($mode_paiement) }}</span>
        </div>
        @endif
    </div>

    <div class="separator"></div>
    <div class="center footer-note">Merci pour votre visite</div>
    <div class="center small muted">{{ $companyName }}</div>
</div>

</body>
</html>

