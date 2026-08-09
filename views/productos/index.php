<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-4">
    <h1 class="text-center mb-4 fw-light" style="letter-spacing:2px;font-size:2.2rem;">
        Nuestra Colección
    </h1>

    <form method="GET" class="row g-2 mb-4 justify-content-center">
        <input type="hidden" name="c" value="Producto">
        <input type="hidden" name="m" value="index">
        <div class="col-md-6 col-lg-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Buscar producto..." value="<?= htmlspecialchars($busqueda) ?>">
                <?php if ($categoriaId): ?>
                    <input type="hidden" name="categoria" value="<?= $categoriaId ?>">
                <?php endif; ?>
            </div>
        </div>
        <div class="col-auto">
            <button class="btn btn-dark"><i class="fas fa-filter me-1"></i> Buscar</button>
        </div>
    </form>

    <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
        <a href="/?c=Producto&m=index<?= $busqueda ? '&q=' . urlencode($busqueda) : '' ?>"
           class="btn btn-sm rounded-pill <?= !$categoriaId ? 'btn-dark' : 'btn-outline-dark' ?>">Todos</a>
        <?php foreach ($categorias as $cat): ?>
            <a href="/?c=Producto&m=index&categoria=<?= $cat['id'] ?><?= $busqueda ? '&q=' . urlencode($busqueda) : '' ?>"
               class="btn btn-sm rounded-pill <?= $categoriaId === (int)$cat['id'] ? 'btn-dark' : 'btn-outline-dark' ?>">
                <?= htmlspecialchars($cat['nombre']) ?> (<?= $cat['total_productos'] ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <p class="text-muted text-center small mb-4"><?= $total ?> producto(s) encontrado(s)</p>

    <?php if (empty($productos)): ?>
        <div class="text-center py-5">
            <p class="text-muted fs-5">No hay productos disponibles.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($productos as $p): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <a href="/?c=Producto&m=ver&id=<?= $p['id'] ?>" class="product-img-wrapper d-block">
                            <img src="<?= htmlspecialchars($p['imagen']) ?>"
                                 alt="<?= htmlspecialchars($p['nombre']) ?>"
                                 class="product-img-fluid"
                                 loading="lazy"
                                 onerror="this.src='https://via.placeholder.com/400x500?text=Body'">
                            <?php if ($p['categoria_nombre']): ?>
                                <span class="badge bg-white text-dark position-absolute top-0 start-0 m-2"><?= htmlspecialchars($p['categoria_nombre']) ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="product-info">
                            <h3 class="product-name">
                                <a href="/?c=Producto&m=ver&id=<?= $p['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($p['nombre']) ?></a>
                            </h3>
                            <p class="product-price">$<?= number_format($p['precio']) ?></p>
                            <?php $disponible = array_filter($p['tallas'] ?? [], fn($t) => (int)$t['stock'] > 0); ?>
                            <?php if (empty($disponible)): ?>
                                <button class="btn-add-cart" disabled>Sin stock</button>
                            <?php else: ?>
                                <button class="btn-add-cart" onclick="abrirTallas(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre'])) ?>', <?= (int)$p['precio'] ?>, '<?= htmlspecialchars(addslashes($p['imagen'])) ?>', <?= json_encode(array_map(fn($t) => ['talla' => $t['talla'], 'stock' => (int)$t['stock']], $disponible)) ?>)">
                                    <i class="fas fa-shopping-bag me-1"></i> Agregar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

        <?php if ($paginas > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $paginas; $i++): ?>
                        <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                            <a class="page-link" href="/?c=Producto&m=index&pagina=<?= $i ?><?= $categoriaId ? '&categoria=' . $categoriaId : '' ?><?= $busqueda ? '&q=' . urlencode($busqueda) : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal selector de talla -->
<div class="modal fade" id="modalTallas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">Agregar al carrito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-3 mb-3">
                    <img id="modal-img" src="" alt="" style="width:64px;height:80px;object-fit:cover;border-radius:8px;">
                    <div>
                        <div id="modal-nombre" class="fw-semibold"></div>
                        <div id="modal-precio" class="text-muted small"></div>
                    </div>
                </div>
                <label class="form-label small text-muted">Selecciona tu talla</label>
                <div id="modal-tallas" class="d-flex flex-wrap gap-2 mb-2"></div>
                <div class="form-text" id="modal-info"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-dark w-100" id="modal-confirmar">
                    <i class="fas fa-shopping-bag me-1"></i> Agregar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/cart.js"></script>
<script>
    let modalSeleccion = { id: null, talla: null };

    function abrirTallas(id, nombre, precio, imagen, tallas) {
        modalSeleccion = { id, talla: null };
        document.getElementById('modal-img').src = imagen;
        document.getElementById('modal-nombre').textContent = nombre;
        document.getElementById('modal-precio').textContent = '$' + precio.toLocaleString();
        document.getElementById('modal-info').textContent = '';

        const cont = document.getElementById('modal-tallas');
        cont.innerHTML = '';
        tallas.forEach(t => {
            const b = document.createElement('button');
            b.className = 'btn btn-sm btn-outline-dark rounded-pill talla-btn';
            b.textContent = t.talla + ' (' + t.stock + ')';
            b.dataset.talla = t.talla;
            b.addEventListener('click', () => {
                cont.querySelectorAll('.talla-btn').forEach(x => x.classList.remove('btn-dark'));
                b.classList.add('btn-dark');
                modalSeleccion.talla = t.talla;
                document.getElementById('modal-info').textContent = 'Talla seleccionada: ' + t.talla;
            });
            cont.appendChild(b);
        });

        const modal = new bootstrap.Modal(document.getElementById('modalTallas'));
        modal.show();
    }

    document.getElementById('modal-confirmar').addEventListener('click', () => {
        if (!modalSeleccion.talla) {
            Swal.fire({ icon: 'warning', title: 'Selecciona una talla', toast: true, position: 'top-end', showConfirmButton: false, timer: 1800 });
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById('modalTallas')).hide();
        addToCart(modalSeleccion.id, modalSeleccion.talla);
    });
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
