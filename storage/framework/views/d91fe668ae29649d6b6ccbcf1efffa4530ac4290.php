<?php $__env->startSection('content'); ?>
<style>
.settings-grid { display:grid; grid-template-columns:1.2fr .8fr; gap:14px; }
.settings-card { background:#fff; border:1px solid #dbe7e1; border-radius:16px; box-shadow:0 10px 24px rgba(18,40,30,.08); }
.settings-head { padding:12px 14px; border-bottom:1px solid #e2ece7; font-weight:800; color:#183026; }
.settings-body { padding:14px; }
@media (max-width: 992px) { .settings-grid { grid-template-columns:1fr; } }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Parametrage systeme</h2>
        <span class="badge bg-success-subtle text-success">Entreprise et exports</span>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="settings-grid">
        <section class="settings-card">
            <div class="settings-head">Informations generales entreprise</div>
            <div class="settings-body">
                <?php if(!auth()->user()->hasPermission('settings.entreprise')): ?>
                    <div class="alert alert-warning mb-0">Votre profil ne permet pas de modifier les informations entreprise.</div>
                <?php else: ?>
                <form method="POST" action="<?php echo e(route('settings.company.update')); ?>" class="row g-2" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="col-md-12">
                        <label class="form-label">Logo entreprise</label>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img src="<?php echo e(asset($settings['company_logo'] ?? 'logo.png')); ?>" alt="Logo entreprise" style="max-height:64px;max-width:220px;object-fit:contain;border:1px solid #dbe7e1;border-radius:10px;padding:6px;background:#fff;">
                            <small class="text-muted">PNG/JPG/WEBP - max 4 Mo</small>
                        </div>
                        <input type="file" name="company_logo" class="form-control" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Nom entreprise</label>
                        <input type="text" name="company_name" class="form-control" value="<?php echo e(old('company_name', $settings['company_name'] ?? env('COMPANY_NAME', config('app.name')))); ?>" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="company_address" class="form-control" value="<?php echo e(old('company_address', $settings['company_address'] ?? env('COMPANY_ADDRESS'))); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telephone</label>
                        <input type="text" name="company_phone" class="form-control" value="<?php echo e(old('company_phone', $settings['company_phone'] ?? env('COMPANY_PHONE'))); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="company_email" class="form-control" value="<?php echo e(old('company_email', $settings['company_email'] ?? env('COMPANY_EMAIL'))); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ICE</label>
                        <input type="text" name="company_ice" class="form-control" value="<?php echo e(old('company_ice', $settings['company_ice'] ?? env('COMPANY_ICE'))); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">RC</label>
                        <input type="text" name="company_rc" class="form-control" value="<?php echo e(old('company_rc', $settings['company_rc'] ?? env('COMPANY_RC'))); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">IF</label>
                        <input type="text" name="company_if" class="form-control" value="<?php echo e(old('company_if', $settings['company_if'] ?? env('COMPANY_IF'))); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">CNSS</label>
                        <input type="text" name="company_cnss" class="form-control" value="<?php echo e(old('company_cnss', $settings['company_cnss'] ?? env('COMPANY_CNSS'))); ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Imprimante etiquette (Windows - optionnel)</label>
                        <input type="text" name="label_printer_name" class="form-control" value="<?php echo e(old('label_printer_name', $settings['label_printer_name'] ?? env('LABEL_PRINTER_NAME'))); ?>" placeholder="Ex: Zebra ZD220, Xprinter Label...">
                        <small class="text-muted">Si vide, le systeme utilise l'imprimante par defaut de Windows via QZ Tray.</small>
                    </div>
                    <div class="col-12 d-grid mt-2">
                        <button class="btn btn-success">Enregistrer entreprise</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </section>

        <section class="settings-card">
            <div class="settings-head">Exportation des donnees (Excel)</div>
            <div class="settings-body">
                <?php if(!auth()->user()->hasPermission('settings.exports')): ?>
                    <div class="alert alert-warning mb-0">Votre profil ne permet pas d'exporter les donnees.</div>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('settings.export.excel')); ?>" class="row g-2">
                        <?php echo csrf_field(); ?>
                        <div class="col-12">
                            <label class="form-label">Dataset a exporter</label>
                            <select name="dataset" class="form-select" required>
                                <option value="">Choisir...</option>
                                <?php $__currentLoopData = $exports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($meta['label']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted">Format CSV UTF-8 compatible Excel.</small>
                        </div>
                        <div class="col-12 d-grid mt-2">
                            <button class="btn btn-dark">Telecharger Excel</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Projets POS\Projets clients\systemphone\resources\views/settings/index.blade.php ENDPATH**/ ?>