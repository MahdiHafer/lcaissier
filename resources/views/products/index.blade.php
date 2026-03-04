@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestion des produits</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('products.create') }}" class="btn btn-primary">Ajouter un produit</a>
            <form method="POST" action="{{ route('products.recalculateStock') }}" class="d-inline" onsubmit="return confirm('Recalculer le stock global des produits a variantes ?');">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">Recalcul stock global</button>
            </form>
            <a href="{{ route('products.index', ['rupture' => 1]) }}" class="btn btn-outline-danger">Rupture de stock</a>
        </div>
    </div>

    <form method="GET" action="{{ route('products.index') }}" class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-select">
                    <option value="">Categorie</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string)request('category_id') === (string)$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="etat" class="form-select">
                    <option value="">Etat</option>
                    <option value="Neuf" {{ request('etat') == 'Neuf' ? 'selected' : '' }}>Neuf</option>
                    <option value="Occasion" {{ request('etat') == 'Occasion' ? 'selected' : '' }}>Occasion</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="fournisseur_id" class="form-select">
                    <option value="">Fournisseur</option>
                    @foreach(\App\Fournisseur::orderBy('nom')->get() as $fournisseur)
                        <option value="{{ $fournisseur->id }}" {{ request('fournisseur_id') == $fournisseur->id ? 'selected' : '' }}>{{ $fournisseur->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filtrer</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('products.index') }}" class="btn btn-light w-100">X</a>
            </div>
        </div>
    </form>

    <div class="table-responsive card p-2">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Image</th>
                <th>Reference</th>
                <th>Code-barres</th>
                <th>Designation</th>
                <th>Categorie</th>
                <th>Variantes</th>
                <th>Etat</th>
                <th>Prix vente</th>
                <th>Stock</th>
                <th>Variantes</th>
                <th>Fournisseur</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td>
                        @if($product->image)
                            <img src="{{ asset($product->image) }}" alt="img" style="width:56px;height:56px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $product->reference ?: '-' }}</td>
                    <td>{{ $product->codebar ?: '-' }}</td>
                    <td>{{ $product->marque }}</td>
                    <td>{{ $product->category ? $product->category->name : ($product->categorie ?: 'Produit') }}</td>
                    <td>
                        <span class="badge {{ $product->has_variants ? 'bg-success' : 'bg-secondary' }}">
                            {{ $product->has_variants ? 'Oui' : 'Non' }}
                        </span>
                    </td>
                    <td>{{ $product->etat }}</td>
                    <td>{{ number_format($product->prix_vente, 2) }} DH</td>
                    <td>{{ $product->quantite }}</td>
                    <td>
                        @if($product->variants->count())
                            @foreach($product->variants as $variant)
                                <div class="small">{{ $variant->size ?: '-' }} / {{ optional($variant->color)->name ?: '-' }} : {{ $variant->quantity }}</div>
                            @endforeach
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $product->fournisseur ? $product->fournisseur->nom : '-' }}</td>
                    <td class="text-end">
                        <a href="{{ route('products.printLabel', $product) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Etiquette</a>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                        @if(auth()->user()->role === 'admin')
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce produit ?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center text-muted">Aucun produit trouve</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
