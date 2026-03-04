<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket</title>
    <style>
        body {
            width: 80mm;
            margin: 0 auto;
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .small { font-size: 10px; }
        .logo {
            max-height: 50px;
            margin-bottom: 5px;
        }
        .line {
            border-top: 1px dashed #333;
            margin: 6px 0;
        }
        table {
            width: 100%;
            font-size: 11px;
        }
        td {
            padding: 2px 0;
        }
        .product-name {
            font-weight: bold;
        }
        .product-line {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body onload="window.print();">

    {{-- LOGO --}}
    <div class="center">
        <img src="{{ asset('logo.png') }}" class="logo" alt="Logo">
    </div>

    {{-- DATE --}}
    <div class="center small">{{ now()->format('d/m/Y H:i') }}</div>

    {{-- CLIENT --}}
    @if (!empty($client['nom']) || !empty($client['telephone']))
    <div class="line"></div>
    <div class="small">
        @if (!empty($client['nom']))
            <div><strong>Client :</strong> {{ $client['nom'] }}</div>
        @endif
        @if (!empty($client['telephone']))
            <div><strong>Tél :</strong> {{ $client['telephone'] }}</div>
        @endif
    </div>
    @endif

    {{-- PRODUITS --}}
    <div class="line"></div>
    @foreach ($panier as $item)
        <div style="margin-bottom: 5px;">
            <div class="product-name">{{ $item['nom'] }}</div>
            <div class="product-line">
                <span>{{ $item['quantite'] }} x {{ number_format($item['prix'], 2) }}</span>
                <span>{{ number_format($item['prix'] * $item['quantite'], 2) }} DH</span>
            </div>
        </div>
    @endforeach

@php
    $remise = $client['remise'] ?? null;
    $typeRemise = $client['type_remise'] ?? null;
    $net = $client['net_a_payer'] ?? $total;
@endphp



    {{-- TOTAL + PAIEMENT --}}
    <div class="line"></div>
@if ($remise)
    <div class="product-line">
        <span>Remise</span>
        <span>
            @if ($typeRemise === '%')
                {{ $remise }} %
            @else
                {{ number_format($remise, 2) }} DH
            @endif
        </span>
    </div>
    <div class="product-line bold">
    <span>Net à payer</span>
    <span>{{ number_format($net, 2) }} DH</span>
</div>
@else


    <div class="product-line bold">
        <span>Total</span>
        <span>{{ number_format($total, 2) }} DH</span>
    </div>
@endif


    @if (!empty($mode_paiement))
    <div class="product-line">
        <span>Paiement</span>
        <span>{{ ucfirst($mode_paiement) }}</span>
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="line"></div>
    <div class="center small">Merci pour votre visite</div>

</body>
</html>
