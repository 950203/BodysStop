<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-light mb-0" style="letter-spacing:1px;">
            Pedido #<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?>
        </h1>
        <a href="/?c=AdminPedido&m=index" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Volver</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><strong><i class="fas fa-user me-2"></i> Cliente</strong></div>
                <div class="card-body small">
                    <p class="mb-1"><strong>Nombre:</strong> <?= Security::escape($pedido['nombre_cliente']) ?></p>
                    <p class="mb-1"><strong>Correo:</strong> <?= Security::escape($pedido['email']) ?></p>
                    <p class="mb-1"><strong>Dirección:</strong> <?= Security::escape($pedido['direccion']) ?></p>
                    <p class="mb-1"><strong>Pago:</strong>
                        <?php if ($pedido['metodo_pago'] === 'nequi'): ?>
                            <span class="badge bg-success">Nequi</span>
                        <?php elseif ($pedido['metodo_pago'] === 'daviplata'): ?>
                            <span class="badge bg-danger">Daviplata</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">—</span>
                        <?php endif; ?>
                    </p>
                    <p class="mb-1"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><strong><i class="fas fa-truck me-2"></i> Estado del pedido</strong></div>
                <div class="card-body">
                    <?php if (Auth::rol() === Auth::ROL_ADMIN): ?>
                        <select class="form-select estado-select mb-3" data-id="<?= $pedido['id'] ?>">
                            <?php foreach (['pendiente', 'pagado', 'en_camino', 'entregado', 'cancelado'] as $e): ?>
                                <option value="<?= $e ?>" <?= $pedido['estado'] === $e ? 'selected' : '' ?>><?= match ($e) { 'en_camino' => 'En camino', default => ucfirst($e) } ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                    <span class="badge <?= match ($pedido['estado']) {
                        'pagado' => 'bg-success',
                        'en_camino' => 'bg-primary',
                        'entregado' => 'bg-secondary',
                        'cancelado' => 'bg-danger',
                        default => 'bg-warning text-dark',
                    } ?>"><?= match ($pedido['estado']) { 'en_camino' => 'En camino', default => Security::escape($pedido['estado']) } ?></span>
                    <?php if (Auth::rol() !== Auth::ROL_ADMIN): ?>
                        <small class="d-block text-muted mt-2"><i class="fas fa-lock me-1"></i>El estado solo puede cambiarlo el administrador.</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><strong><i class="fas fa-box-open me-2"></i> Detalle</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Talla</th>
                                <th>Cant.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedido['detalle'] as $d): ?>
                                <tr>
                                    <td>
                                        <img src="<?= Security::escape($d['producto_imagen']) ?>" alt="" style="width:36px;height:36px;object-fit:cover;border-radius:6px;" class="me-2">
                                        <?= Security::escape($d['producto_nombre']) ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= Security::escape($d['talla']) ?></span></td>
                                    <td><?= $d['cantidad'] ?></td>
                                    <td class="text-end">$<?= number_format($d['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total</td>
                                <td class="text-end fw-bold">$<?= number_format($pedido['total']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelector('.estado-select').addEventListener('change', async function () {
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
            Swal.fire({ icon: 'success', title: res.mensaje, timer: 1200, showConfirmButton: false })
                .then(() => location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.error });
        }
    });
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
