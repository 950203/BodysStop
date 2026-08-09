<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="small">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/?c=Producto&m=index" class="text-decoration-none">Tienda</a></li>
            <?php if (!empty($producto['categoria_nombre'])): ?>
                <li class="breadcrumb-item active"><?= htmlspecialchars($producto['categoria_nombre']) ?></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($producto['nombre']) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-md-6">
            <img src="<?= htmlspecialchars($producto['imagen']) ?>"
                 alt="<?= htmlspecialchars($producto['nombre']) ?>"
                 class="w-100 rounded-4 shadow-sm"
                 style="object-fit:cover;max-height:560px;"
                 onerror="this.src='https://via.placeholder.com/600x750?text=Body'">
        </div>

        <div class="col-md-6">
            <h1 class="fw-light mb-2" style="letter-spacing:1px;"><?= htmlspecialchars($producto['nombre']) ?></h1>
            <p class="fs-3 fw-bold mb-3">$<?= number_format($producto['precio']) ?></p>

            <?php if ((int)$promedio['total'] > 0): ?>
                <div class="mb-3 d-flex align-items-center gap-2">
                    <span class="text-warning fs-5"><?= str_repeat('★', (int)round($promedio['promedio'])) ?><span class="text-muted"><?= str_repeat('☆', 5 - (int)round($promedio['promedio'])) ?></span></span>
                    <span class="small text-muted"><?= number_format((float)$promedio['promedio'], 1) ?> · <?= $promedio['total'] ?> reseña(s)</span>
                </div>
            <?php endif; ?>

            <p class="text-muted"><?= nl2br(htmlspecialchars($producto['descripcion'] ?? '')) ?></p>

            <?php $disponibles = array_filter($producto['tallas'] ?? [], fn($t) => (int)$t['stock'] > 0); ?>
            <?php if (empty($disponibles)): ?>
                <div class="alert alert-warning py-2 small">Este producto está agotado.</div>
            <?php else: ?>
                <div class="mb-3">
                    <label class="form-label small text-muted">Talla</label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($producto['tallas'] as $t): ?>
                            <?php if ((int)$t['stock'] > 0): ?>
                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill talla-radio" data-talla="<?= htmlspecialchars($t['talla']) ?>">
                                    <?= htmlspecialchars($t['talla']) ?>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" disabled title="Agotada">
                                    <?= htmlspecialchars($t['talla']) ?> <i class="fas fa-times"></i>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="input-group" style="width:130px;">
                        <button class="btn btn-outline-dark" type="button" onclick="cambiarCantidad(-1)">−</button>
                        <input type="number" id="detalle-cantidad" class="form-control text-center" value="1" min="1" max="10" readonly>
                        <button class="btn btn-outline-dark" type="button" onclick="cambiarCantidad(1)">+</button>
                    </div>
                    <button class="btn btn-dark btn-lg flex-fill" id="btn-agregar-detalle">
                        <i class="fas fa-shopping-bag me-1"></i> Agregar al carrito
                    </button>
                </div>
            <?php endif; ?>

            <a href="/?c=Producto&m=index" class="text-decoration-none small text-muted"><i class="fas fa-arrow-left me-1"></i>Volver a la tienda</a>
        </div>
    </div>

    <hr class="my-5">

    <div class="row g-5">
        <div class="col-lg-7">
            <h2 class="fw-light mb-4" style="letter-spacing:1px;">Reseñas de clientes</h2>

            <?php if (empty($resenas)): ?>
                <p class="text-muted">Aún no hay reseñas para este producto.</p>
            <?php else: ?>
                <?php foreach ($resenas as $r): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="fw-semibold"><?= htmlspecialchars($r['nombre']) ?></span>
                                    <span class="text-warning small ms-2"><?= str_repeat('★', (int)$r['calificacion']) ?><?= str_repeat('☆', 5 - (int)$r['calificacion']) ?></span>
                                </div>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($r['created_at'])) ?></small>
                            </div>
                            <p class="mb-0 mt-2"><?= htmlspecialchars($r['comentario']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><strong><i class="fas fa-star me-2"></i> Escribe tu reseña</strong></div>
                <div class="card-body">
                    <?php if (!Auth::check()): ?>
                        <p class="text-muted small mb-0">
                            <a href="/?c=Auth&m=login">Inicia sesión</a> para dejar tu opinión.
                        </p>
                    <?php elseif ($yaResenio): ?>
                        <p class="text-muted small mb-0"><i class="fas fa-check-circle text-success me-1"></i>Ya reseñaste este producto.</p>
                    <?php elseif (!$puedeReseniar): ?>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle me-1"></i>Solo los clientes que compraron este producto pueden reseñarlo.
                        </p>
                    <?php else: ?>
                        <form id="resena-form">
                            <input type="hidden" name="producto_id" value="<?= (int)$producto['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Tu calificación</label>
                                <div id="resena-estrellas" class="fs-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star" data-val="<?= $i ?>" style="cursor:pointer;">☆</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="calificacion" id="resena-calificacion" value="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Comentario</label>
                                <textarea name="comentario" class="form-control" rows="4" minlength="3" required placeholder="Cuéntanos qué tal te quedó..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-dark w-100"><i class="fas fa-paper-plane me-1"></i> Publicar reseña</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/js/cart.js"></script>
<script>
    let detalleTalla = null;

    function cambiarCantidad(delta) {
        const input = document.getElementById('detalle-cantidad');
        const nuevo = Math.min(10, Math.max(1, parseInt(input.value || 1) + delta));
        input.value = nuevo;
    }

    document.querySelectorAll('.talla-radio').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.talla-radio').forEach(x => x.classList.remove('btn-dark'));
            btn.classList.add('btn-dark');
            detalleTalla = btn.dataset.talla;
        });
    });

    document.getElementById('btn-agregar-detalle').addEventListener('click', () => {
        if (!detalleTalla) {
            Swal.fire({ icon: 'warning', title: 'Selecciona una talla', toast: true, position: 'top-end', showConfirmButton: false, timer: 1800 });
            return;
        }
        const qty = parseInt(document.getElementById('detalle-cantidad').value || 1);
        addToCart(<?= (int)$producto['id'] ?>, detalleTalla, qty);
    });

    const resenaForm = document.getElementById('resena-form');
    if (resenaForm) {
        const estrellas = document.querySelectorAll('#resena-estrellas .star');
        const inputCal = document.getElementById('resena-calificacion');

        estrellas.forEach(star => {
            star.addEventListener('click', () => {
                const val = parseInt(star.dataset.val);
                inputCal.value = val;
                estrellas.forEach(s => s.textContent = parseInt(s.dataset.val) <= val ? '★' : '☆');
            });
        });

        resenaForm.addEventListener('submit', async e => {
            e.preventDefault();
            if (!inputCal.value || parseInt(inputCal.value) < 1) {
                Swal.fire({ icon: 'warning', title: 'Selecciona una calificación', toast: true, position: 'top-end', showConfirmButton: false, timer: 1800 });
                return;
            }
            const h = { 'Content-Type': 'application/x-www-form-urlencoded' };
            if (window.API_TOKEN) h['X-Auth-Token'] = window.API_TOKEN;
            if (window.CSRF_TOKEN) h['X-CSRF-Token'] = window.CSRF_TOKEN;

            const res = await fetch('/?c=Resena&m=store', {
                method: 'POST',
                headers: h,
                body: new URLSearchParams(new FormData(resenaForm)),
            }).then(r => r.json());

            if (res.ok) {
                Swal.fire({ icon: 'success', title: res.mensaje, timer: 1500, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.error });
            }
        });
    }
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
