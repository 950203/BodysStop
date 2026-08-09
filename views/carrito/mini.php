<?php if (empty($items)): ?>
    <p class="mini-empty">Tu carrito está vacío</p>
<?php else: ?>

    <?php foreach ($items as $item): ?>
        <?php $clave = htmlspecialchars($item['clave']); ?>
        <div class="mini-item" id="mini-item-<?= $clave ?>">
            <span><?= $item['nombre'] ?> <small class="text-muted">(<?= htmlspecialchars($item['talla']) ?>)</small></span>

            <div class="qty-controls">
                <button onclick="changeMiniQty('<?= $clave ?>', -1)">−</button>
                <span class="qty"><?= $item['qty'] ?></span>
                <button onclick="changeMiniQty('<?= $clave ?>', 1)">+</button>
                <small>× $<?= number_format($item['precio']) ?></small>
            </div>

            <button onclick="removeMini('<?= $clave ?>')">❌</button>
        </div>
    <?php endforeach ?>

    <div class="mini-total">
        Total: $<?= number_format($total) ?>
    </div>

    <a href="/?c=Carrito&m=index" class="mini-btn">Ver carrito</a>

<?php endif; ?>
