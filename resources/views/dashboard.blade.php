@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-black mb-4">Dashboard Ventes</h2>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="text-black">Date début</label>
            <input type="date" name="start" class="form-control" value="{{ $start }}">
        </div>
        <div class="col-md-4">
            <label class="text-black">Date fin</label>
            <input type="date" name="end" class="form-control" value="{{ $end }}">
        </div>
        <div class="col-md-4 align-self-end">
            <button type="submit" class="btn btn-drphone w-100">Filtrer</button>
        </div>
    </form>

    <div class="row mb-4">
<div class="col-md-6">
    <div class="bg-light text-black p-4 rounded shadow-sm">
        <h5>Chiffre d'affaires (net)</h5>
        <h3 class="text-success">{{ number_format($ca_net, 2) }} DH</h3>
        <small class="text-muted">
            Brut : {{ number_format($ca_net + $ventes->sum('remise'), 2) }} DH |
            Remises : {{ number_format($ventes->sum('remise'), 2) }} DH
        </small>
    </div>
</div>
        <div class="col-md-6">
            <div class="bg-light text-black p-4 rounded shadow-sm">
                <h5>Marge</h5>
                <h3 class="text-info">{{ number_format($marge, 2) }} DH</h3>
            </div>
        </div>
    </div>
<div class="row mb-4">
    <div class="col-md-6">
        <div class="bg-light text-black p-4 rounded shadow-sm">
            <h5>Valeur du stock globale</h5>
            <h3 class="text-warning">{{ number_format($valeur_stock, 2) }} DH</h3>

            @if($valeur_stock_par_categorie->count())
                <div class="mt-3">
                    <h6 class="mb-2 text-black-100">Par catégorie :</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($valeur_stock_par_categorie as $cat)
                            <span class="badge bg-none px-3 py-2">
                                <h5 class="text-black">{{ ucfirst($cat->categorie) }}</h5>
                                <h6 class="text-info">{{ number_format($cat->total, 2) }} DH</h6>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-md-6">
        <div class="bg-light text-black p-4 rounded shadow-sm">
            <h5>Articles en stock</h5>
            <h3 class="text-success">{{ $total_articles }} unités</h3>
        </div>
    </div>

</div>

    <div class="bg-light text-black p-4 rounded shadow-sm">
        <h5>Top 20 produits les plus vendus</h5>
        <table class="table table-light table-bordered mt-3">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>Quantité vendue</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($topProduits as $produit)
                    <tr>
                        <td>{{ $produit['designation'] }}</td>
                        <td>{{ $produit['quantite'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
