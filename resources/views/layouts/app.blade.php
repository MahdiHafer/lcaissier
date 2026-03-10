<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', "L'CAISSIER") }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/fusion-pos-theme.css') }}" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
@php
    $appLogo = asset($companySettings['logo'] ?? 'logo.png');
    $u = auth()->user();
@endphp
<div class="app-shell">
@auth
    <nav class="navbar navbar-expand-lg topbar px-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('caisse.index') }}">
                <img src="{{ $appLogo }}" alt="Logo">
            </a>

            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarMenu">
                <ul class="navbar-nav align-items-lg-center mt-2 mt-lg-0">
                    @if ($u->hasPermission('dashboard.view'))
                    <li class="nav-item nav-pill">
                        <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}">Dashboard</a>
                    </li>
                    @endif

                    @if ($u->hasPermission('caisse.use'))
                    <li class="nav-item nav-pill">
                        <a href="{{ route('caisse.index') }}" class="nav-link {{ request()->is('caisse*') ? 'active' : '' }}">Caisse</a>
                    </li>
                    @endif

                    @if ($u->hasPermission('catalog.products.view') || $u->hasPermission('catalog.stock.view') || $u->hasPermission('catalog.categories.manage') || $u->hasPermission('catalog.colors.manage'))
                    <li class="nav-item dropdown nav-pill">
                        <a class="nav-link dropdown-toggle {{ request()->is('products*') || request()->is('stock*') || request()->is('inventory*') || request()->is('categories*') || request()->is('colors*') ? 'active' : '' }}"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Catalogue</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if ($u->hasPermission('catalog.products.view'))
                            <li><a class="dropdown-item" href="{{ route('products.index', ['type' => 'produit']) }}">Produits</a></li>
                            @endif
                            @if ($u->hasPermission('catalog.stock.view'))
                            <li><a class="dropdown-item" href="{{ route('stock.dashboard') }}">Stock</a></li>
                            @endif
                            @if ($u->hasPermission('catalog.categories.manage') || $u->hasPermission('catalog.colors.manage'))
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            @if ($u->hasPermission('catalog.categories.manage'))
                                <li><a class="dropdown-item" href="{{ route('categories.index') }}">Categories</a></li>
                            @endif
                            @if ($u->hasPermission('catalog.colors.manage'))
                                <li><a class="dropdown-item" href="{{ route('colors.index') }}">Couleurs</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if ($u->hasPermission('tiers.clients.view') || $u->hasPermission('tiers.fournisseurs.view') || $u->hasPermission('tiers.fournisseurs.create'))
                    <li class="nav-item dropdown nav-pill">
                        <a class="nav-link dropdown-toggle {{ request()->is('clients*') || request()->is('fournisseurs*') ? 'active' : '' }}"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Tiers</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if ($u->hasPermission('tiers.clients.view'))
                            <li><a class="dropdown-item" href="{{ route('clients.index') }}">Clients</a></li>
                            @endif
                            @if ($u->hasPermission('tiers.fournisseurs.view'))
                                <li><a class="dropdown-item" href="{{ route('fournisseurs.index') }}">Fournisseurs</a></li>
                            @elseif ($u->hasPermission('tiers.fournisseurs.create'))
                                <li><a class="dropdown-item" href="{{ route('fournisseurs.create') }}">Nouveau Fournisseur</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if ($u->hasPermission('documents.ventes.view') || $u->hasPermission('documents.bl.view') || $u->hasPermission('documents.devis.view') || $u->hasPermission('documents.factures.view') || $u->hasPermission('documents.avoirs.view'))
                    <li class="nav-item dropdown nav-pill">
                        <a class="nav-link dropdown-toggle {{ request()->is('ventes*') || request()->is('bons-livraison*') || request()->is('devis*') || request()->is('avoirs*') || request()->is('factures*') ? 'active' : '' }}"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Documents</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if ($u->hasPermission('documents.ventes.view'))
                            <li><a class="dropdown-item" href="{{ route('ventes.historique') }}">Ventes</a></li>
                            @endif
                            @if ($u->hasPermission('documents.bl.view'))
                            <li><a class="dropdown-item" href="{{ route('bons-livraison.index') }}">BL</a></li>
                            @endif
                            @if ($u->hasPermission('documents.devis.view'))
                            <li><a class="dropdown-item" href="{{ route('devis.index') }}">Devis</a></li>
                            @endif
                            @if ($u->hasPermission('documents.factures.view'))
                            <li><a class="dropdown-item" href="{{ route('factures.index') }}">Factures</a></li>
                            @endif
                            @if ($u->hasPermission('documents.avoirs.view'))
                            <li><a class="dropdown-item" href="{{ route('avoirs.index') }}">Avoirs</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if ($u->hasPermission('settings.entreprise') || $u->hasPermission('settings.exports') || $u->hasPermission('settings.users.manage'))
                    <li class="nav-item dropdown nav-pill">
                        <a class="nav-link dropdown-toggle {{ request()->is('settings*') || request()->is('users*') ? 'active' : '' }}"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Parametres</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if ($u->hasPermission('settings.entreprise') || $u->hasPermission('settings.exports'))
                            <li><a class="dropdown-item" href="{{ route('settings.index') }}">Entreprise</a></li>
                            @endif
                            @if ($u->hasPermission('settings.users.manage'))
                                <li><a class="dropdown-item" href="{{ route('users.index') }}">Utilisateurs</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-app btn-app-primary btn-sm">Deconnexion</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="app-main">
        @yield('content')
    </main>
@endauth

@guest
    <main class="container py-5">
        @yield('content')
    </main>
@endguest
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const targets = document.querySelectorAll('.card, .table-responsive, .alert, h1, h2, h3, h4, h5');
    targets.forEach((el, idx) => {
        el.classList.add('reveal');
        el.style.animationDelay = `${Math.min(idx * 35, 280)}ms`;
    });
})();
</script>
@yield('scripts')
@stack('scripts')
</body>
</html>
