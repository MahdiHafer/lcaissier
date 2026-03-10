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
<?php
    $appLogo = asset($companySettings['logo'] ?? 'logo.png');
    $u = auth()->user();
?>
<div class="app-shell">
<?php if(auth()->guard()->check()): ?>
    <nav class="navbar navbar-expand-lg topbar px-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo e(route('caisse.index')); ?>">
                <img src="<?php echo e($appLogo); ?>" alt="Logo">
            </a>

            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarMenu">
                <ul class="navbar-nav align-items-lg-center mt-2 mt-lg-0">
                    <?php if($u->hasPermission('dashboard.view')): ?>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('dashboard.index')); ?>" class="nav-link <?php echo e(request()->is('dashboard*') ? 'active' : ''); ?>">Dashboard</a>
                    </li>
                    <?php endif; ?>

                    <?php if($u->hasPermission('caisse.use')): ?>
                    <li class="nav-item nav-pill">
                        <a href="<?php echo e(route('caisse.index')); ?>" class="nav-link <?php echo e(request()->is('caisse*') ? 'active' : ''); ?>">Caisse</a>
                    </li>
                    <?php endif; ?>

                    <?php if($u->hasPermission('catalog.products.view') || $u->hasPermission('catalog.stock.view') || $u->hasPermission('catalog.categories.manage') || $u->hasPermission('catalog.colors.manage')): ?>
                    <li class="nav-item dropdown nav-pill">
                        <a class="nav-link dropdown-toggle <?php echo e(request()->is('products*') || request()->is('stock*') || request()->is('inventory*') || request()->is('categories*') || request()->is('colors*') ? 'active' : ''); ?>"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Catalogue</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if($u->hasPermission('catalog.products.view')): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('products.index', ['type' => 'produit'])); ?>">Produits</a></li>
                            <?php endif; ?>
                            <?php if($u->hasPermission('catalog.stock.view')): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('stock.dashboard')); ?>">Stock</a></li>
                            <?php endif; ?>
                            <?php if($u->hasPermission('catalog.categories.manage') || $u->hasPermission('catalog.colors.manage')): ?>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <?php if($u->hasPermission('catalog.categories.manage')): ?>
                                <li><a class="dropdown-item" href="<?php echo e(route('categories.index')); ?>">Categories</a></li>
                            <?php endif; ?>
                            <?php if($u->hasPermission('catalog.colors.manage')): ?>
                                <li><a class="dropdown-item" href="<?php echo e(route('colors.index')); ?>">Couleurs</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if($u->hasPermission('tiers.clients.view') || $u->hasPermission('tiers.fournisseurs.view') || $u->hasPermission('tiers.fournisseurs.create')): ?>
                    <li class="nav-item dropdown nav-pill">
                        <a class="nav-link dropdown-toggle <?php echo e(request()->is('clients*') || request()->is('fournisseurs*') ? 'active' : ''); ?>"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Tiers</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if($u->hasPermission('tiers.clients.view')): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('clients.index')); ?>">Clients</a></li>
                            <?php endif; ?>
                            <?php if($u->hasPermission('tiers.fournisseurs.view')): ?>
                                <li><a class="dropdown-item" href="<?php echo e(route('fournisseurs.index')); ?>">Fournisseurs</a></li>
                            <?php elseif($u->hasPermission('tiers.fournisseurs.create')): ?>
                                <li><a class="dropdown-item" href="<?php echo e(route('fournisseurs.create')); ?>">Nouveau Fournisseur</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if($u->hasPermission('documents.ventes.view') || $u->hasPermission('documents.bl.view') || $u->hasPermission('documents.devis.view') || $u->hasPermission('documents.factures.view') || $u->hasPermission('documents.avoirs.view')): ?>
                    <li class="nav-item dropdown nav-pill">
                        <a class="nav-link dropdown-toggle <?php echo e(request()->is('ventes*') || request()->is('bons-livraison*') || request()->is('devis*') || request()->is('avoirs*') || request()->is('factures*') ? 'active' : ''); ?>"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Documents</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if($u->hasPermission('documents.ventes.view')): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('ventes.historique')); ?>">Ventes</a></li>
                            <?php endif; ?>
                            <?php if($u->hasPermission('documents.bl.view')): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('bons-livraison.index')); ?>">BL</a></li>
                            <?php endif; ?>
                            <?php if($u->hasPermission('documents.devis.view')): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('devis.index')); ?>">Devis</a></li>
                            <?php endif; ?>
                            <?php if($u->hasPermission('documents.factures.view')): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('factures.index')); ?>">Factures</a></li>
                            <?php endif; ?>
                            <?php if($u->hasPermission('documents.avoirs.view')): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('avoirs.index')); ?>">Avoirs</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if($u->hasPermission('settings.entreprise') || $u->hasPermission('settings.exports') || $u->hasPermission('settings.users.manage')): ?>
                    <li class="nav-item dropdown nav-pill">
                        <a class="nav-link dropdown-toggle <?php echo e(request()->is('settings*') || request()->is('users*') ? 'active' : ''); ?>"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Parametres</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if($u->hasPermission('settings.entreprise') || $u->hasPermission('settings.exports')): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('settings.index')); ?>">Entreprise</a></li>
                            <?php endif; ?>
                            <?php if($u->hasPermission('settings.users.manage')): ?>
                                <li><a class="dropdown-item" href="<?php echo e(route('users.index')); ?>">Utilisateurs</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

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