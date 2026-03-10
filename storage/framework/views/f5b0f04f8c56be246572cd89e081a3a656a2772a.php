<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Historique des ventes</h2>

    <form method="GET" action="<?php echo e(route('ventes.historique')); ?>" class="card p-3 mb-4">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="client" value="<?php echo e(request('client')); ?>" class="form-control" placeholder="Client">
            </div>
            <div class="col-md-3">
                <input type="text" name="fournisseur" value="<?php echo e(request('fournisseur')); ?>" class="form-control" placeholder="Fournisseur">
            </div>
            <div class="col-md-2">
                <input type="text" name="produit" value="<?php echo e(request('produit')); ?>" class="form-control" placeholder="Produit">
            </div>
            <div class="col-md-2">
                <input type="date" name="date" value="<?php echo e(request('date')); ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <select name="paiement" class="form-select">
                    <option value="">Paiement</option>
                    <option value="Especes" <?php echo e(request('paiement') === 'Especes' ? 'selected' : ''); ?>>Especes</option>
                    <option value="Virement" <?php echo e(request('paiement') === 'Virement' ? 'selected' : ''); ?>>Virement</option>
                    <option value="Credit" <?php echo e(request('paiement') === 'Credit' ? 'selected' : ''); ?>>Credit</option>
                    <option value="Cheque" <?php echo e(request('paiement') === 'Cheque' ? 'selected' : ''); ?>>Cheque</option>
                    <option value="TPE" <?php echo e(request('paiement') === 'TPE' ? 'selected' : ''); ?>>TPE</option>
                    <option value="Avoir" <?php echo e(request('paiement') === 'Avoir' ? 'selected' : ''); ?>>Avoir</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Rechercher</button>
            </div>
            <div class="col-md-1">
                <a href="<?php echo e(route('ventes.historique')); ?>" class="btn btn-light w-100">X</a>
            </div>
        </div>
    </form>

    <?php if(auth()->user()->role === 'admin'): ?>
        <div class="alert alert-info text-center">Total net: <strong><?php echo e(number_format($totalNet, 2)); ?> DH</strong></div>
    <?php endif; ?>

    <div class="table-responsive card p-2">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Ticket</th>
                <th>Date</th>
                <th>Client</th>
                <th>Total</th>
                <th>Remise</th>
                <th>Net</th>
                <th>Paiement</th>
                <th>Paye</th>
                <th>Reste</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $ventes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($vente->numero_ticket); ?></td>
                    <td><?php echo e($vente->created_at->format('d/m/Y H:i')); ?></td>
                    <td>
                        <?php if($vente->clientInfo): ?>
                            <?php echo e($vente->clientInfo->nom); ?>

                            <div class="small text-muted"><?php echo e($vente->clientInfo->telephone); ?></div>
                        <?php else: ?>
                            <span class="text-muted">Comptoir</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e(number_format($vente->total, 2)); ?> DH</td>
                    <td><?php echo e(number_format($vente->remise, 2)); ?> DH</td>
                    <td class="fw-bold text-success"><?php echo e(number_format($vente->net_a_payer, 2)); ?> DH</td>
                    <td><?php echo e($vente->mode_paiement); ?></td>
                    <td><?php echo e(number_format($vente->montant_paye, 2)); ?> DH</td>
                    <td class="<?php echo e($vente->net_a_payer - $vente->montant_paye > 0 ? 'text-warning fw-bold' : ''); ?>"><?php echo e(number_format($vente->net_a_payer - $vente->montant_paye, 2)); ?> DH</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo e($vente->id); ?>">Details</button>
                        <a href="<?php echo e(route('ventes.facture.create', $vente)); ?>" class="btn btn-sm btn-outline-success">Facture</a>
                        <a href="<?php echo e(route('ventes.avoir.create', $vente)); ?>" class="btn btn-sm btn-outline-warning">Avoir</a>
                        <?php if(auth()->user()->role === 'admin'): ?>
                            <a href="<?php echo e(route('ventes.edit', $vente->id)); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="<?php echo e(route('ventes.destroy', $vente->id)); ?>" class="d-inline" onsubmit="return confirm('Supprimer cette vente ?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="10" class="text-center text-muted">Aucune vente</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-3"><?php echo e($ventes->links()); ?></div>
</div>

<?php $__currentLoopData = $ventes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="detailsModal<?php echo e($vente->id); ?>" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Details ticket <?php echo e($vente->numero_ticket); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantite</th>
                        <th>PU</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $vente->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($d->nom_produit); ?><div class="small text-muted">Ref: <?php echo e($d->reference_produit); ?></div></td>
                            <td><?php echo e($d->quantite); ?></td>
                            <td><?php echo e(number_format($d->prix_unitaire, 2)); ?> DH</td>
                            <td><?php echo e(number_format($d->total_ligne, 2)); ?> DH</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Projets POS\Projets clients\systemphone\resources\views/ventes/historique.blade.php ENDPATH**/ ?>