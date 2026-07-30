<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="cart-container">
    <h1 class="text-center mb-4 fw-light" style="letter-spacing:1px;">Tu Carrito</h1>

    <?php if (empty($items)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-bag"></i>
            <p class="fs-5 text-muted">Tu carrito está vacío</p>
            <a href="/?c=Producto&m=index" class="btn btn-dark mt-2">Ver tienda</a>
        </div>
    <?php else: ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr id="row-<?= $item['id'] ?>">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= htmlspecialchars($item['imagen']) ?>"
                                     alt="<?= htmlspecialchars($item['nombre']) ?>"
                                     style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                                <span class="fw-semibold"><?= htmlspecialchars($item['nombre']) ?></span>
                            </div>
                        </td>
                        <td>$<?= number_format($item['precio']) ?></td>
                        <td>
                            <button class="qty-btn" onclick="restar(<?= $item['id'] ?>)">−</button>
                            <span class="mx-2 fw-bold"><?= $item['qty'] ?></span>
                            <button class="qty-btn" onclick="sumar(<?= $item['id'] ?>)">+</button>
                        </td>
                        <td class="subtotal fw-bold">$<?= number_format($item['subtotal']) ?></td>
                        <td>
                            <button class="btn-remove" onclick="removeItem(<?= $item['id'] ?>)">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>

        <div class="cart-total">
            <strong>Total: $<?= number_format($total) ?></strong>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="/?c=Producto&m=index" class="btn btn-outline-dark">
                <i class="fas fa-arrow-left me-1"></i> Seguir comprando
            </a>
            <a href="/?c=Checkout&m=index" class="btn btn-dark">
                Ir al checkout <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/js/cart.js"></script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
