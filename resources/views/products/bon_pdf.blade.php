<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
    </style>
</head>
<body>
    <h2>Bon de Produits</h2>
    <p>Date : {{ $date }}</p>

    <table>
        <thead>
            <tr>
                <th>Code-barres</th>
                <th>Désignation</th>
                <th>Catégorie</th>
                <th>État</th>
                <th>Prix Achat</th>
                <th>Prix Vente</th>
                <th>Quantité</th>
                <th>Fournisseur</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produits as $p)
            <tr>
                <td>{{ $p->codebar }}</td>
                <td>{{ $p->marque }} {{ $p->modele }}</td>
                <td>{{ $p->categorie }}</td>
                <td>{{ $p->etat }}</td>
                <td>{{ number_format($p->prix_achat, 2) }} DH</td>
                <td>{{ number_format($p->prix_vente, 2) }} DH</td>
                <td>{{ $p->quantite }}</td>
                <td>{{ $p->fournisseur->nom ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
