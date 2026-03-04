<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis {{ $devis->numero }}</title>
    <style>
        @page { size: A4; margin: 24mm 20mm 28mm 20mm; }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            color: #182024;
            margin: 0;
            font-size: 12px;
            padding: 2mm 0;
        }

        .page-content { padding-left: 4mm; padding-right: 4mm; }
        .header { display: table; width: 100%; margin-bottom: 14px; }
        .header-left, .header-right { display: table-cell; vertical-align: top; }
        .header-left { width: 58%; }
        .header-right { width: 42%; }
        .logo { max-height: 72px; max-width: 230px; object-fit: contain; }

        .document-title {
            margin-top: 10px;
            font-size: 23px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: #0b4f6a;
        }

        .doc-box { border: 1px solid #d6e0e8; border-radius: 8px; padding: 10px 12px; background: #f9fbfd; }
        .doc-box-row { display: flex; justify-content: space-between; margin-bottom: 6px; gap: 8px; font-size: 12px; }
        .doc-box-row:last-child { margin-bottom: 0; }
        .doc-label { color: #5e6d78; font-weight: 600; }
        .doc-value { color: #182024; font-weight: 700; text-align: right; }

        .client-card {
            margin-top: 8px;
            margin-bottom: 12px;
            border-left: 3px solid #0b4f6a;
            background: #f4f9fc;
            padding: 8px 10px;
            border-radius: 6px;
        }

        .client-title { font-size: 11px; color: #5e6d78; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 3px; font-weight: 700; }
        .client-name { font-size: 13px; font-weight: 700; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th {
            background: #eef5fa;
            color: #1f2a30;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .3px;
            border: 1px solid #d6e0e8;
            padding: 8px 6px;
        }
        tbody td { border: 1px solid #e1e8ee; padding: 8px 6px; vertical-align: middle; }
        tbody tr:nth-child(even) { background: #fbfdff; }
        .text-end { text-align: right; }

        .img {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border: 1px solid #d7dfe6;
            border-radius: 6px;
            display: block;
            margin: 0 auto;
        }

        .total-box {
            margin-top: 12px;
            margin-left: auto;
            width: 280px;
            border: 1px solid #d6e0e8;
            border-radius: 8px;
            overflow: hidden;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            padding: 8px 10px;
            font-size: 12px;
            border-bottom: 1px solid #e7edf2;
            background: #ffffff;
        }

        .total-line:last-child { border-bottom: none; }
        .total-final { font-size: 14px; font-weight: 700; background: #eef5fa; }

        .notes {
            margin-top: 14px;
            font-size: 12px;
            white-space: pre-wrap;
            border: 1px solid #e1e8ee;
            border-radius: 8px;
            padding: 9px 10px;
            background: #fcfeff;
        }

        .footer {
            position: fixed;
            left: 20mm;
            right: 20mm;
            bottom: 10mm;
            border-top: 1px solid #d6e0e8;
            padding-top: 6px;
            text-align: center;
            font-size: 10px;
            color: #5e6d78;
            line-height: 1.4;
        }

        .company-name { font-weight: 700; color: #1f2a30; }
    </style>
</head>
<body onload="window.print()">
@php
    $companyName = env('COMPANY_NAME', config('app.name', "L'CAISSIER"));
    $companyAddress = env('COMPANY_ADDRESS', 'Adresse entreprise');
    $companyPhone = env('COMPANY_PHONE', 'Telephone');
    $companyEmail = env('COMPANY_EMAIL', 'Email');
@endphp

<div class="page-content">
    <div class="header">
        <div class="header-left">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="logo">
            <div class="document-title">Devis</div>
        </div>
        <div class="header-right">
            <div class="doc-box">
                <div class="doc-box-row">
                    <span class="doc-label">Numero Devis</span>
                    <span class="doc-value">{{ $devis->numero }}</span>
                </div>
                <div class="doc-box-row">
                    <span class="doc-label">Date</span>
                    <span class="doc-value">{{ \Carbon\Carbon::parse($devis->date_devis)->format('d/m/Y') }}</span>
                </div>
                <div class="doc-box-row">
                    <span class="doc-label">Client</span>
                    <span class="doc-value">{{ optional($devis->client)->nom ?: 'Client comptoir' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="client-card">
        <div class="client-title">Destinataire</div>
        <div class="client-name">{{ optional($devis->client)->nom ?: 'Client comptoir' }}</div>
        @if(optional($devis->client)->telephone)
            <div>{{ $devis->client->telephone }}</div>
        @endif
    </div>

    <table>
        <thead>
        <tr>
            <th style="width:70px;">Image</th>
            <th>Designation</th>
            <th style="width:70px;">Qte</th>
            <th style="width:120px;">Prix unitaire</th>
            <th style="width:120px;">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($devis->details as $line)
            <tr>
                <td>
                    @if($line->image)
                        <img class="img" src="{{ asset($line->image) }}" alt="">
                    @else
                        <span style="color:#7a8792;">-</span>
                    @endif
                </td>
                <td>{{ $line->designation }}</td>
                <td class="text-end">{{ $line->quantite }}</td>
                <td class="text-end">{{ number_format($line->prix_unitaire, 2) }} DH</td>
                <td class="text-end">{{ number_format($line->total_ligne, 2) }} DH</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <div class="total-line">
            <span>Sous-total</span>
            <strong>{{ number_format($devis->total, 2) }} DH</strong>
        </div>
        <div class="total-line total-final">
            <span>Total Devis</span>
            <strong>{{ number_format($devis->total, 2) }} DH</strong>
        </div>
    </div>

    @if(!empty($devis->notes))
        <div class="notes"><strong>Notes :</strong><br>{{ $devis->notes }}</div>
    @endif
</div>

<div class="footer">
    <div class="company-name">{{ $companyName }}</div>
    <div>{{ $companyAddress }}</div>
    <div>{{ $companyPhone }} @if(!empty($companyEmail)) | {{ $companyEmail }} @endif</div>
</div>
</body>
</html>
