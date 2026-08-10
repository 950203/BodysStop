<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="main-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 fw-bold"><i class="fas fa-boxes me-2"></i> Gestión de Stock</h2>
            <span class="badge bg-dark"><i class="fas fa-user-tag me-1"></i> <?= Security::escape(Auth::nombre()) ?></span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="80">Miniatura</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Stock</th>
                        <th>Precio</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                        <?php
                            $stockArr = [];
                            foreach (explode(',', $p['stock_detalle'] ?? '') as $det) {
                                if ($det === '') continue;
                                [$detTalla, $detStock] = explode(':', $det);
                                $stockArr[$detTalla] = (int)$detStock;
                            }
                            $tallasJson = htmlspecialchars(json_encode($stockArr), ENT_QUOTES);
                        ?>
                        <tr>
                            <td>
                                <img src="<?= $p['imagen'] ?>" class="product-img border" alt="producto">
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($p['nombre']) ?></div>
                                <small class="text-muted">SKU: <?= str_pad($p['id'], 5, "0", STR_PAD_LEFT) ?></small>
                            </td>
                            <td><?= !empty($p['marca']) ? '<span class="badge bg-dark">' . htmlspecialchars($p['marca']) . '</span>' : '<span class="text-muted">—</span>' ?></td>
                            <td>
                                <?php $stock = (int)($p['stock_total'] ?? 0); ?>
                                <span class="badge <?= $stock > 0 ? 'bg-success' : 'bg-danger' ?>">
                                    <i class="fas <?= $stock > 0 ? 'fa-box' : 'fa-times-circle' ?> me-1"></i><?= $stock ?> unidades
                                </span>
                                <?php if (!empty($p['stock_detalle'])): ?>
                                    <div class="mt-1">
                                        <?php foreach (explode(',', $p['stock_detalle']) as $det): ?>
                                            <?php [$detTalla, $detStock] = explode(':', $det); ?>
                                            <span class="badge bg-light text-dark border" title="Talla <?= $detTalla ?>">
                                                <?= $detTalla ?>: <?= $detStock ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">
                                    $<?= number_format($p['precio'], 2) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <button onclick="abrirStock(<?= $p['id'] ?>, <?= $tallasJson ?>)"
                                    class="btn btn-outline-success btn-action" title="Editar stock">
                                    <i class="fas fa-boxes me-1"></i> Editar stock
                                </button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($productos)): ?>
            <div class="alert alert-light text-center border-dashed py-5">
                <i class="fas fa-search fa-2x text-muted mb-2"></i>
                <p class="mb-0">No se encontraron productos.</p>
            </div>
        <?php endif; ?>

        <?php if ($paginas > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $paginas; $i++): ?>
                        <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                            <a class="page-link" href="/?c=AdminProducto&m=stock&pagina=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function apiHeaders() {
        const h = { 'Content-Type': 'application/x-www-form-urlencoded' };
        if (window.API_TOKEN) h['X-Auth-Token'] = window.API_TOKEN;
        if (window.CSRF_TOKEN) h['X-CSRF-Token'] = window.CSRF_TOKEN;
        return h;
    }

    let stockProductoId = null;

    function abrirStock(id, tallas) {
        stockProductoId = id;
        const cont = document.getElementById('modal-stock-campos');
        cont.innerHTML = '';
        const orden = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        orden.forEach(t => {
            const valor = tallas[t] ?? 0;
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 align-items-center';
            row.innerHTML = `
                <div class="col-4"><span class="badge bg-light text-dark border">${t}</span></div>
                <div class="col-8">
                    <input type="number" name="stock[${t}]" class="form-control form-control-sm" value="${valor}" min="0">
                </div>`;
            cont.appendChild(row);
        });
        new bootstrap.Modal(document.getElementById('modalStock')).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('btn-guardar-stock');
        if (!btn) return;
        btn.addEventListener('click', async function () {
            const form = document.getElementById('modal-stock-form');
            const data = new URLSearchParams(new FormData(form));
            data.append('id', stockProductoId);

            const res = await fetch('/?c=AdminProducto&m=actualizarStock', {
                method: 'POST',
                headers: apiHeaders(),
                body: data
            }).then(r => r.json());

            if (res.ok) {
                bootstrap.Modal.getInstance(document.getElementById('modalStock')).hide();
                Swal.fire({ icon: 'success', title: res.mensaje, toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.error });
            }
        });
    });
</script>

<!-- Modal editar stock -->
<div class="modal fade" id="modalStock" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold"><i class="fas fa-boxes me-2"></i> Editar stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="modal-stock-form">
                <div class="modal-body">
                    <div class="row g-2 mb-2">
                        <div class="col-4"><small class="text-muted">Talla</small></div>
                        <div class="col-8"><small class="text-muted">Unidades</small></div>
                    </div>
                    <div id="modal-stock-campos"></div>
                    <small class="text-muted">Guarda para aplicar los cambios de stock.</small>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-dark w-100" id="btn-guardar-stock">
                        <i class="fas fa-save me-1"></i> Guardar stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
