<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestion des produits</h2>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('products.create')); ?>" class="btn btn-primary">Ajouter un produit</a>
            <form method="POST" action="<?php echo e(route('products.recalculateStock')); ?>" class="d-inline" onsubmit="return confirm('Recalculer le stock global des produits a variantes ?');">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-secondary">Recalcul stock global</button>
            </form>
            <a href="<?php echo e(route('products.index', ['rupture' => 1])); ?>" class="btn btn-outline-danger">Rupture de stock</a>
        </div>
    </div>

    <form method="GET" action="<?php echo e(route('products.index')); ?>" class="card p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?php echo e(request('search')); ?>">
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-select">
                    <option value="">Categorie</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php echo e((string)request('category_id') === (string)$category->id ? 'selected' : ''); ?>><?php echo e($category->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="etat" class="form-select">
                    <option value="">Etat</option>
                    <option value="Neuf" <?php echo e(request('etat') == 'Neuf' ? 'selected' : ''); ?>>Neuf</option>
                    <option value="Occasion" <?php echo e(request('etat') == 'Occasion' ? 'selected' : ''); ?>>Occasion</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="fournisseur_id" class="form-select">
                    <option value="">Fournisseur</option>
                    <?php $__currentLoopData = \App\Fournisseur::orderBy('nom')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fournisseur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($fournisseur->id); ?>" <?php echo e(request('fournisseur_id') == $fournisseur->id ? 'selected' : ''); ?>><?php echo e($fournisseur->nom); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filtrer</button>
            </div>
            <div class="col-md-1">
                <a href="<?php echo e(route('products.index')); ?>" class="btn btn-light w-100">X</a>
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
            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <?php if($product->image): ?>
                            <img src="<?php echo e(asset($product->image)); ?>" alt="img" style="width:56px;height:56px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($product->reference ?: '-'); ?></td>
                    <td><?php echo e($product->codebar ?: '-'); ?></td>
                    <td><?php echo e($product->marque); ?></td>
                    <td><?php echo e($product->category ? $product->category->name : ($product->categorie ?: 'Produit')); ?></td>
                    <td>
                        <span class="badge <?php echo e($product->has_variants ? 'bg-success' : 'bg-secondary'); ?>">
                            <?php echo e($product->has_variants ? 'Oui' : 'Non'); ?>

                        </span>
                    </td>
                    <td><?php echo e($product->etat); ?></td>
                    <td><?php echo e(number_format($product->prix_vente, 2)); ?> DH</td>
                    <td><?php echo e($product->quantite); ?></td>
                    <td>
                        <?php if($product->variants->count()): ?>
                            <?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="small d-flex align-items-center gap-2 mb-1">
                                    <span><?php echo e($variant->size ?: '-'); ?> / <?php echo e(optional($variant->color)->name ?: '-'); ?> : <?php echo e($variant->quantity); ?></span>
                                    <a href="<?php echo e(route('products.printLabel', ['product' => $product, 'variant_id' => $variant->id])); ?>" target="_blank" class="btn btn-xs btn-outline-secondary" style="padding:1px 6px;font-size:11px;">Etiq.</a>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($product->fournisseur ? $product->fournisseur->nom : '-'); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('products.printLabel', $product)); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Etiquette</a>
                        <a href="<?php echo e(route('products.edit', $product)); ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <?php if(auth()->user()->role === 'admin'): ?>
                            <form action="<?php echo e(route('products.destroy', $product)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce produit ?');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="12" class="text-center text-muted">Aucun produit trouve</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Projets POS\Projets clients\systemphone\resources\views/products/index.blade.php ENDPATH**/ ?>