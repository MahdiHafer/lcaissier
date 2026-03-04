<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Étiquette Produit</title>
    <style>
        /* Taille réelle du ticket : 50 x 25 mm */
        @page {
            size: 50mm 25mm;
            margin: 0;
        }

        html, body {
            width: 50mm;
            height: 25mm;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 8px;
            overflow: hidden;
        }

        .wrapper {
            width: 48mm;          /* légèrement moins que 50 pour ne rien couper */
            height: 23mm;         /* légèrement moins que 25 */
            margin: 1mm auto;     /* centre sur le ticket */
            box-sizing: border-box;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            padding: 0;
        }

        .marque {
            font-weight: bold;
            font-size: 8px;
        }

        .price {
            font-size: 9px;
        }

        .barcode img {
            width: 42mm;
            height: 12mm;
        }

        .barcode-text {
            font-size: 8px;
            letter-spacing: 2px;
            margin-top: -2px;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="wrapper">
        <div class="marque">{{ $product->marque }}</div>
        <div class="price">{{ number_format($product->prix_vente, 2) }} DH</div>

        <div class="barcode">
            <img src="{{ $barcodeSrc ?? ('data:image/png;base64,' . $barcode) }}" alt="Code-barres">
        </div>

        <div class="barcode-text">{{ $barcodeCode }}</div>
    </div>
</body>
</html>
