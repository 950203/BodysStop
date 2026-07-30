<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="text-center mb-5 fw-light" style="letter-spacing:2px;font-size:2.2rem;">
        Nuestra Colección
    </h1>

    <?php if (empty($productos)): ?>
        <div class="text-center py-5">
            <p class="text-muted fs-5">No hay productos disponibles aún.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($productos as $p): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <div class="product-img-wrapper">
                            <img src="<?= htmlspecialchars($p['imagen']) ?>"
                                 alt="<?= htmlspecialchars($p['nombre']) ?>"
                                 class="product-img-fluid"
                                 loading="lazy"
                                 onerror="this.src='https://via.placeholder.com/400x500?text=Body'">
                            <button class="btn-add-cart"
                                    onclick="addToCart(<?= $p['id'] ?>)">
                                <i class="fas fa-shopping-bag me-1"></i> Agregar
                            </button>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($p['nombre']) ?></h3>
                            <p class="product-price">$<?= number_format($p['precio']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/js/cart.js"></script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
