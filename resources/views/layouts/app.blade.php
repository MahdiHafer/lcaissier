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
<div class="app-shell">
@auth
    <nav class="navbar navbar-expand-lg topbar px-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('caisse.index') }}">
                <img src="{{ asset('logo.png') }}" alt="Logo">
            </a>

            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarMenu">
                <ul class="navbar-nav align-items-lg-center mt-2 mt-lg-0">
                    @if (auth()->user()->role === 'admin')
                    <li class="nav-item nav-pill">
                        <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}">Dashboard</a>
                    </li>
                    @endif

                    <li class="nav-item nav-pill">
                        <a href="{{ route('caisse.index') }}" class="nav-link {{ request()->is('caisse*') ? 'active' : '' }}">Caisse</a>
                    </li>

                    <li class="nav-item nav-pill">
                        <a href="{{ route('products.index', ['type' => 'produit']) }}" class="nav-link {{ request()->is('products*') ? 'active' : '' }}">Produits</a>
                    </li>
                    <li class="nav-item nav-pill">
                        <a href="{{ route('stock.dashboard') }}" class="nav-link {{ request()->is('stock*') || request()->is('inventory*') ? 'active' : '' }}">Stock</a>
                    </li>

                    @if (auth()->user()->role === 'admin')
                    <li class="nav-item nav-pill">
                        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->is('categories*') ? 'active' : '' }}">Categories</a>
                    </li>
                    <li class="nav-item nav-pill">
                        <a href="{{ route('colors.index') }}" class="nav-link {{ request()->is('colors*') ? 'active' : '' }}">Couleurs</a>
                    </li>
                    @endif

                    <li class="nav-item nav-pill">
                        <a href="{{ route('clients.index') }}" class="nav-link {{ request()->is('clients*') ? 'active' : '' }}">Clients</a>
                    </li>

                    @php $role = auth()->user()->role; @endphp
                    @if ($role === 'admin')
                    <li class="nav-item nav-pill">
                        <a href="{{ route('fournisseurs.index') }}" class="nav-link {{ request()->is('fournisseurs*') ? 'active' : '' }}">Fournisseurs</a>
                    </li>
                    @elseif ($role === 'agent')
                    <li class="nav-item nav-pill">
                        <a href="{{ route('fournisseurs.create') }}" class="nav-link {{ request()->is('fournisseurs/create') ? 'active' : '' }}">Nouveau Fournisseur</a>
                    </li>
                    @endif

                    @if (auth()->user()->role === 'admin')
                    <li class="nav-item nav-pill">
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->is('users*') ? 'active' : '' }}">Utilisateurs</a>
                    </li>
                    @endif

                    <li class="nav-item nav-pill">
                        <a href="{{ route('ventes.historique') }}" class="nav-link {{ request()->is('ventes*') ? 'active' : '' }}">Ventes</a>
                    </li>
                    <li class="nav-item nav-pill">
                        <a href="{{ route('bons-livraison.index') }}" class="nav-link {{ request()->is('bons-livraison*') ? 'active' : '' }}">BL</a>
                    </li>
                    <li class="nav-item nav-pill">
                        <a href="{{ route('devis.index') }}" class="nav-link {{ request()->is('devis*') ? 'active' : '' }}">Devis</a>
                    </li>
                    <li class="nav-item nav-pill">
                        <a href="{{ route('avoirs.index') }}" class="nav-link {{ request()->is('avoirs*') ? 'active' : '' }}">Avoirs</a>
                    </li>
                    <li class="nav-item nav-pill">
                        <a href="{{ route('factures.index') }}" class="nav-link {{ request()->is('factures*') ? 'active' : '' }}">Factures</a>
                    </li>

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
