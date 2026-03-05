<?php if (empty($items)): ?>
    <p class="mini-empty">Tu carrito está vacío</p>
<?php else: ?>

    <?php foreach ($items as $item): ?>
        <div class="mini-item" id="mini-item-<?= $item['id'] ?>">
            <span><?= $item['nombre'] ?></span>

            <div class="qty-controls">
                <button onclick="changeMiniQty(<?= $item['id'] ?>, -1)">−</button>
                <span class="qty"><?= $item['qty'] ?></span>
                <button onclick="changeMiniQty(<?= $item['id'] ?>, 1)">+</button>
                <small>× $<?= number_format($item['precio']) ?></small>
            </div>

            <button onclick="removeMini(<?= $item['id'] ?>)">❌</button>
        </div>
    <?php endforeach ?>

    <div class="mini-total">
        Total: $<?= number_format($total) ?>
    </div>

    <a href="/?c=Carrito&m=index" class="mini-btn">Ver carrito</a>

<?php endif; ?>