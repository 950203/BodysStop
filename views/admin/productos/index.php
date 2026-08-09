<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="main-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 fw-bold"><i class="fas fa-boxes me-2"></i> Gestión de Productos</h2>
            <a href="/?c=AdminProducto&m=create" class="btn btn-dark shadow-sm">
                <i class="fas fa-plus me-1"></i> Nuevo Producto
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="80">Miniatura</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                        <tr id="row-<?= $p['id'] ?>">
                            <td>
                                <img src="<?= $p['imagen'] ?>" class="product-img border" alt="producto">
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($p['nombre']) ?></div>
                                <small class="text-muted">SKU: <?= str_pad($p['id'], 5, "0", STR_PAD_LEFT) ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['categoria_nombre'] ?? '—') ?></span></td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">
                                    $<?= number_format($p['precio'], 2) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ((int)$p['activo'] === 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button onclick="toggleProducto(<?= $p['id'] ?>, <?= (int)$p['activo'] ?>)"
                                    class="btn btn-outline-secondary btn-action me-1" title="<?= (int)$p['activo'] === 1 ? 'Ocultar' : 'Activar' ?>">
                                    <i class="fas <?= (int)$p['activo'] === 1 ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                </button>
                                <a href="/?c=AdminProducto&m=edit&id=<?= $p['id'] ?>"
                                    class="btn btn-outline-primary btn-action me-1" title="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <button onclick="deleteProduct(<?= $p['id'] ?>)"
                                    class="btn btn-outline-danger btn-action" title="Ocultar">
                                    <i class="fas fa-trash-alt"></i>
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
                <p class="mb-0">No se encontraron productos en la base de datos.</p>
            </div>
        <?php endif; ?>

        <?php if ($paginas > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $paginas; $i++): ?>
                        <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                            <a class="page-link" href="/?c=AdminProducto&m=index&pagina=<?= $i ?>"><?= $i ?></a>
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

    function deleteProduct(id) {
        Swal.fire({
            title: '¿Ocultar producto?',
            text: "El producto dejará de venderse en la tienda, pero no se borra.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ocultar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/?c=AdminProducto&m=delete', {
                        method: 'POST',
                        headers: apiHeaders(),
                        body: 'id=' + id
                    })
                    .then(response => response.json())
                    .then(data => {
                        const row = document.getElementById('row-' + id);
                        if (row) {
                            row.classList.add('row-fade-out');
                            setTimeout(() => row.remove(), 400);
                        }
                        Swal.fire({ title: data.mensaje || 'Hecho', icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                    });
            }
        });
    }

    function toggleProducto(id, activo) {
        fetch('/?c=AdminProducto&m=toggleActivo', {
                method: 'POST',
                headers: apiHeaders(),
                body: 'id=' + id + '&activo=' + (activo === 1 ? 0 : 1)
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) location.reload();
            });
    }
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
