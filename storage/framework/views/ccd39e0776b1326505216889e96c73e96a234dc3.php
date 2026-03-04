<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket</title>
    <style>
        body {
            width: 80mm;
            margin: 0 auto;
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .small { font-size: 10px; }
        .logo {
            max-height: 50px;
            margin-bottom: 5px;
        }
        .line {
            border-top: 1px dashed #333;
            margin: 6px 0;
        }
        table {
            width: 100%;
            font-size: 11px;
        }
        td {
            padding: 2px 0;
        }
        .product-name {
            font-weight: bold;
        }
        .product-line {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body onload="window.print();">

    
    <div class="center">
        <img src="<?php echo e(asset('logo.png')); ?>" class="logo" alt="Logo">
    </div>

    
    <div class="center small"><?php echo e(now()->format('d/m/Y H:i')); ?></div>

    
    <?php if(!empty($client['nom']) || !empty($client['telephone'])): ?>
    <div class="line"></div>
    <div class="small">
        <?php if(!empty($client['nom'])): ?>
            <div><strong>Client :</strong> <?php echo e($client['nom']); ?></div>
        <?php endif; ?>
        <?php if(!empty($client['telephone'])): ?>
            <div><strong>Tél :</strong> <?php echo e($client['telephone']); ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    
    <div class="line"></div>
    <?php $__currentLoopData = $panier; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div style="margin-bottom: 5px;">
            <div class="product-name"><?php echo e($item['nom']); ?></div>
            <div class="product-line">
                <span><?php echo e($item['quantite']); ?> x <?php echo e(number_format($item['prix'], 2)); ?></span>
                <span><?php echo e(number_format($item['prix'] * $item['quantite'], 2)); ?> DH</span>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php
    $remise = $client['remise'] ?? null;
    $typeRemise = $client['type_remise'] ?? null;
    $net = $client['net_a_payer'] ?? $total;
?>



    
    <div class="line"></div>
<?php if($remise): ?>
    <div class="product-line">
        <span>Remise</span>
        <span>
            <?php if($typeRemise === '%'): ?>
                <?php echo e($remise); ?> %
            <?php else: ?>
                <?php echo e(number_format($remise, 2)); ?> DH
            <?php endif; ?>
        </span>
    </div>
    <div class="product-line bold">
    <span>Net à payer</span>
    <span><?php echo e(number_format($net, 2)); ?> DH</span>
</div>
<?php else: ?>


    <div class="product-line bold">
        <span>Total</span>
        <span><?php echo e(number_format($total, 2)); ?> DH</span>
    </div>
<?php endif; ?>


    <?php if(!empty($mode_paiement)): ?>
    <div class="product-line">
        <span>Paiement</span>
        <span><?php echo e(ucfirst($mode_paiement)); ?></span>
    </div>
    <?php endif; ?>

    
    <div class="line"></div>
    <div class="center small">Merci pour votre visite</div>

</body>
</html>
<?php /**PATH C:\Projets POS\Projets clients\systemphone\resources\views/caisse/ticket.blade.php ENDPATH**/ ?>