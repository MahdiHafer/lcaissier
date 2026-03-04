<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(config('app.name', "L'CAISSIER")); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo e(asset('css/fusion-pos-theme.css')); ?>" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
<div class="app-shell">
<?php if(auth()->guard()->check()): ?>
    <nav class="navbar navbar-expand-lg topbar px-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo e(route('caisse.index')); ?>">
                <img src="<?php echo e(asset('logo.png')); ?>" alt="Logo">
            </a>

            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarMenu">
                <ul class="navbar-nav align-items-lg-center mt-2 mt-lg-0">
                    <?php if(auth()->user()->role === 'admin'): ?>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('dashboard.index')); ?>" class="nav-link <?php echo e(request()->is('dashboard*') ? 'active' : ''); ?>">Dashboard</a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('caisse.index')); ?>" class="nav-link <?php echo e(request()->is('caisse*') ? 'active' : ''); ?>">Caisse</a>
                    </li>

                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('products.index', ['type' => 'produit'])); ?>" class="nav-link <?php echo e(request()->is('products*') ? 'active' : ''); ?>">Produits</a>
                    </li>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('stock.dashboard')); ?>" class="nav-link <?php echo e(request()->is('stock*') || request()->is('inventory*') ? 'active' : ''); ?>">Stock</a>
                    </li>

                    <?php if(auth()->user()->role === 'admin'): ?>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('categories.index')); ?>" class="nav-link <?php echo e(request()->is('categories*') ? 'active' : ''); ?>">Categories</a>
                    </li>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('colors.index')); ?>" class="nav-link <?php echo e(request()->is('colors*') ? 'active' : ''); ?>">Couleurs</a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('clients.index')); ?>" class="nav-link <?php echo e(request()->is('clients*') ? 'active' : ''); ?>">Clients</a>
                    </li>

                    <?php $role = auth()->user()->role; ?>
                    <?php if($role === 'admin'): ?>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('fournisseurs.index')); ?>" class="nav-link <?php echo e(request()->is('fournisseurs*') ? 'active' : ''); ?>">Fournisseurs</a>
                    </li>
                    <?php elseif($role === 'agent'): ?>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('fournisseurs.create')); ?>" class="nav-link <?php echo e(request()->is('fournisseurs/create') ? 'active' : ''); ?>">Nouveau Fournisseur</a>
                    </li>
                    <?php endif; ?>

                    <?php if(auth()->user()->role === 'admin'): ?>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('users.index')); ?>" class="nav-link <?php echo e(request()->is('users*') ? 'active' : ''); ?>">Utilisateurs</a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('ventes.historique')); ?>" class="nav-link <?php echo e(request()->is('ventes*') ? 'active' : ''); ?>">Ventes</a>
                    </li>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('bons-livraison.index')); ?>" class="nav-link <?php echo e(request()->is('bons-livraison*') ? 'active' : ''); ?>">BL</a>
                    </li>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('devis.index')); ?>" class="nav-link <?php echo e(request()->is('devis*') ? 'active' : ''); ?>">Devis</a>
                    </li>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('avoirs.index')); ?>" class="nav-link <?php echo e(request()->is('avoirs*') ? 'active' : ''); ?>">Avoirs</a>
                    </li>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('factures.index')); ?>" class="nav-link <?php echo e(request()->is('factures*') ? 'active' : ''); ?>">Factures</a>
                    </li>

                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-app btn-app-primary btn-sm">Deconnexion</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="app-main">
        <?php echo $__env->yieldContent('content'); ?>
    </main>
<?php endif; ?>

<?php if(auth()->guard()->guest()): ?>
    <main class="container py-5">
        <?php echo $__env->yieldContent('content'); ?>
    </main>
<?php endif; ?>
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
<?php echo $__env->yieldContent('scripts'); ?>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Projets POS\Projets clients\systemphone\resources\views/layouts/app.blade.php ENDPATH**/ ?>