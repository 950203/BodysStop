<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-light mb-0" style="letter-spacing:1px;">Gestión de Pedidos</h1>
        <div>
            <a href="/?c=AdminPedido&m=dashboard" class="btn btn-outline-dark btn-sm"><i class="fas fa-chart-pie me-1"></i> Dashboard</a>
            <span class="badge bg-dark ms-2"><i class="fas fa-box me-1"></i> <?= Auth::rol() === Auth::ROL_ADMIN ? 'Administrador' : 'Vendedor' ?></span>
        </div>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger py-2 small"><i class="fas fa-exclamation-circle me-1"></i><?= Security::escape($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <input type="hidden" name="c" value="AdminPedido">
                <input type="hidden" name="m" value="index">
                <div class="col-md-4">
                    <input type="text" name="busqueda" class="form-control" placeholder="Buscar por #, cliente o correo..." value="<?= Security::escape($busqueda) ?>">
                </div>
                <div class="col-md-3">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estados as $e): ?>
                            <option value="<?= $e ?>" <?= $estado === $e ? 'selected' : '' ?>><?= ucfirst($e) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-dark w-100"><i class="fas fa-search me-1"></i> Filtrar</button>
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="/?c=AdminPedido&m=index" class="btn btn-outline-secondary w-100">Limpiar</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidos)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No hay pedidos que coincidan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td><strong>#<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                                    <td>
                                        <div class="fw-semibold"><?= Security::escape($pedido['nombre_cliente']) ?></div>
                                        <small class="text-muted"><?= Security::escape($pedido['email']) ?></small>
                                    </td>
                                    <td class="small"><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></td>
                                    <td class="fw-bold">$<?= number_format($pedido['total']) ?></td>
                                    <td>
                                        <select class="form-select form-select-sm estado-select" data-id="<?= $pedido['id'] ?>" style="width:130px;">
                                            <?php foreach ($estados as $e): ?>
                                                <option value="<?= $e ?>" <?= $pedido['estado'] === $e ? 'selected' : '' ?>><?= ucfirst($e) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="text-end">
                                        <a href="/?c=AdminPedido&m=ver&id=<?= $pedido['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Ver detalle"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($paginas > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $paginas; $i++): ?>
                            <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                <a class="page-link" href="/?c=AdminPedido&m=index&pagina=<?= $i ?><?= $estado ? '&estado=' . urlencode($estado) : '' ?><?= $busqueda ? '&busqueda=' . urlencode($busqueda) : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('.estado-select').forEach(select => {
        select.addEventListener('change', async function () {
            const res = await fetch('/?c=AdminPedido&m=cambiarEstado', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Auth-Token': window.API_TOKEN,
                    'X-CSRF-Token': window.CSRF_TOKEN,
                },
                body: new URLSearchParams({ id: this.dataset.id, estado: this.value }),
            }).then(r => r.json());

            if (res.ok) {
                Swal.fire({ icon: 'success', title: res.mensaje, timer: 1200, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.error });
            }
        });
    });
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
