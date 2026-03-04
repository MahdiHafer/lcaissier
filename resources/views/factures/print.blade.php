<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->numero }}</title>
    <style>
        @page { size: A4; margin: 28mm 22mm 34mm 22mm; }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            color: #1c2730;
            font-size: 12px;
            padding: 4mm 0;
        }

        .page-content {
            padding-left: 4mm;
            padding-right: 4mm;
        }

        .header { display: table; width: 100%; margin-bottom: 16px; }
        .left, .right { display: table-cell; vertical-align: top; }
        .left { width: 58%; }
        .right { width: 42%; }
        .logo { max-height: 70px; max-width: 220px; object-fit: contain; }
        .title { font-size: 24px; font-weight: 800; color: #0e4f84; margin-top: 8px; text-transform: uppercase; letter-spacing: .4px; }
        .box { border: 1px solid #d8e1ea; border-radius: 8px; padding: 10px; background: #f8fbff; }
        .row { display: flex; justify-content: space-between; margin-bottom: 6px; gap: 10px; }
        .row:last-child { margin-bottom: 0; }
        .label { color: #5d6e7f; font-weight: 600; }
        .value { font-weight: 700; text-align: right; }

        .cards { display: grid; grid-template-columns: 1fr; gap: 10px; margin-bottom: 12px; }
        .card { border: 1px solid #dbe3ea; border-radius: 8px; padding: 10px; background: #fff; }
        .card-title { font-size: 11px; text-transform: uppercase; color: #718394; font-weight: 700; margin-bottom: 6px; }

        table { width: 100%; border-collapse: collapse; }
        thead th { border: 1px solid #dbe3ea; background: #eef5fb; padding: 8px 6px; font-size: 11px; text-transform: uppercase; }
        tbody td { border: 1px solid #e3ebf2; padding: 8px 6px; }
        tbody tr:nth-child(even) { background: #fbfdff; }
        .text-end { text-align: right; }

        .totals { margin-top: 12px; margin-left: auto; width: 300px; border: 1px solid #dbe3ea; border-radius: 8px; overflow: hidden; }
        .total-line { display: flex; justify-content: space-between; padding: 8px 10px; border-bottom: 1px solid #e8eef4; }
        .total-line:last-child { border-bottom: 0; }
        .total-main { background: #eef5fb; font-weight: 800; font-size: 14px; }

        .footer {
            position: fixed;
            left: 22mm;
            right: 22mm;
            bottom: 12mm;
            border-top: 1px solid #dbe3ea;
            padding-top: 6px;
            text-align: center;
            font-size: 10px;
            color: #647688;
            line-height: 1.5;
        }

        .footer-company {
            font-weight: 700;
            color: #1f2a33;
        }
    </style>
</head>
<body onload="window.print()">
@php
    $footerCompany = $facture->legal_company_name ?: env('COMPANY_NAME', config('app.name'));
    $footerAddress = $facture->legal_address ?: env('COMPANY_ADDRESS');
    $footerPhone = $facture->legal_phone ?: env('COMPANY_PHONE');
    $footerEmail = $facture->legal_email ?: env('COMPANY_EMAIL');
@endphp

<div class="page-content">
    <div class="header">
        <div class="left">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="logo">
            <div class="title">Facture</div>
        </div>
        <div class="right">
            <div class="box">
                <div class="row"><span class="label">Numero</span><span class="value">{{ $facture->numero }}</span></div>
                <div class="row"><span class="label">Date</span><span class="value">{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</span></div>
                <div class="row"><span class="label">BL lie</span><span class="value">{{ $facture->bon_livraison_id ? ('#' . $facture->bon_livraison_id) : '-' }}</span></div>
            </div>
        </div>
    </div>

    <div class="cards">
        <div class="card">
            <div class="card-title">Client</div>
            <div><strong>{{ optional($facture->client)->societe ?: (optional($facture->client)->nom ?: 'Client comptoir') }}</strong></div>
            <div>ICE: {{ optional($facture->client)->ice ?: '-' }}</div>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th>Designation</th>
            <th style="width:70px">Qte</th>
            <th style="width:120px">Prix unitaire</th>
            <th style="width:120px">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($facture->details as $line)
            <tr>
                <td>{{ $line->designation }}</td>
                <td class="text-end">{{ $line->quantite }}</td>
                <td class="text-end">{{ number_format($line->prix_unitaire, 2) }} DH</td>
                <td class="text-end">{{ number_format($line->total_ligne, 2) }} DH</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-line"><span>Total HT</span><strong>{{ number_format($facture->total_ht, 2) }} DH</strong></div>
        <div class="total-line"><span>TVA ({{ number_format($facture->tva_rate, 2) }}%)</span><strong>{{ number_format($facture->tva_amount, 2) }} DH</strong></div>
        <div class="total-line total-main"><span>Total TTC</span><strong>{{ number_format($facture->total_ttc, 2) }} DH</strong></div>
    </div>
</div>

<div class="footer">
    <div class="footer-company">{{ $footerCompany }}</div>
    <div>ICE: {{ $facture->legal_ice ?: '-' }} | RC: {{ $facture->legal_rc ?: '-' }} | IF: {{ $facture->legal_if ?: '-' }} | CNSS: {{ $facture->legal_cnss ?: '-' }}</div>
    <div>{{ $footerAddress ?: '-' }}</div>
    <div>{{ $footerPhone ?: '-' }} @if($footerEmail) | {{ $footerEmail }} @endif</div>
</div>
</body>
</html>
