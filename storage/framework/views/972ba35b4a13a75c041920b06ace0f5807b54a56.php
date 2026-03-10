

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Avoirs Clients</h2>
    </div>

    <form method="GET" class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" value="<?php echo e(request('search')); ?>" placeholder="Numero avoir, client, ticket...">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100">Rechercher</button>
            </div>
            <div class="col-md-1">
                <a href="<?php echo e(route('avoirs.index')); ?>" class="btn btn-light w-100">X</a>
            </div>
        </div>
    </form>

    <div class="table-responsive card p-2">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Numero</th>
                <th>Date</th>
                <th>Client</th>
                <th>Vente</th>
                <th>Total avoir</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $avoirs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $avoir): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($avoir->numero); ?></td>
                    <td><?php echo e(\Carbon\Carbon::parse($avoir->date_avoir)->format('d/m/Y')); ?></td>
                    <td><?php echo e(optional($avoir->client)->nom ?: 'Client comptoir'); ?></td>
                    <td><?php echo e(optional($avoir->vente)->numero_ticket ?: '-'); ?></td>
                    <td class="fw-bold"><?php echo e(number_format($avoir->total, 2)); ?> DH</td>
                    <td class="text-end">
                        <a href="<?php echo e(route('avoirs.print', $avoir)); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Imprimer</a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-muted">Aucun avoir</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-3"><?php echo e($avoirs->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Projets POS\Projets clients\systemphone\resources\views/avoirs/index.blade.php ENDPATH**/ ?>