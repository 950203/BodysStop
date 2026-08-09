<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-light" style="letter-spacing:1px;">Mi Perfil</h1>
        <span class="badge bg-dark"><?= Security::escape($usuario['rol']) ?></span>
    </div>

    <div class="row g-4">

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-user-edit me-2"></i>Mis datos</h5>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Nombre</label>
                        <input type="text" id="perfil-nombre" class="form-control" value="<?= Security::escape($usuario['nombre']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Correo electrónico</label>
                        <input type="email" id="perfil-email" class="form-control" value="<?= Security::escape($usuario['email']) ?>">
                    </div>

                    <button class="btn btn-dark w-100" id="btn-actualizar-perfil">
                        <i class="fas fa-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-lock me-2"></i>Cambiar contraseña</h5>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Contraseña actual</label>
                        <input type="password" id="clave-actual" class="form-control" autocomplete="current-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Nueva contraseña</label>
                        <input type="password" id="clave-nueva" class="form-control" autocomplete="new-password" placeholder="Mín. 8 caracteres, mayúscula, minúscula y número">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Confirmar nueva contraseña</label>
                        <input type="password" id="clave-confirm" class="form-control" autocomplete="new-password">
                    </div>

                    <button class="btn btn-outline-dark w-100" id="btn-cambiar-clave">
                        <i class="fas fa-key me-1"></i> Actualizar contraseña
                    </button>
                </div>
            </div>
        </div>
    </div>

    <h2 class="fw-light my-4" style="letter-spacing:1px;">Mis pedidos</h2>

    <?php if (empty($pedidos)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-box-open fa-3x mb-3"></i>
            <p>Aún no tienes pedidos.</p>
            <a href="/?c=Producto&m=index" class="btn btn-dark">Ir a la tienda</a>
        </div>
    <?php else: ?>
        <?php foreach ($pedidos as $pedido): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span><strong>Pedido #<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></strong></span>
                    <span class="d-flex align-items-center gap-2">
                        <span class="badge <?= match ($pedido['estado']) {
                            'pagado' => 'bg-success',
                            'enviado' => 'bg-primary',
                            'entregado' => 'bg-secondary',
                            'cancelado' => 'bg-danger',
                            default => 'bg-warning text-dark',
                        } ?>"><?= Security::escape($pedido['estado']) ?></span>
                        <span class="text-muted small"><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></span>
                    </span>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-3">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Talla</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedido['detalle'] as $d): ?>
                                <tr>
                                    <td>
                                        <img src="<?= Security::escape($d['producto_imagen']) ?>" alt="" style="width:32px;height:32px;object-fit:cover;border-radius:6px;" class="me-2">
                                        <?= Security::escape($d['producto_nombre']) ?>
                                    </td>
                                    <td><?= Security::escape($d['talla']) ?></td>
                                    <td><?= $d['cantidad'] ?></td>
                                    <td>$<?= number_format($d['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="text-end">
                        <span class="fw-bold">Total: $<?= number_format($pedido['total']) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const API_TOKEN = window.API_TOKEN;
    const CSRF_TOKEN = window.CSRF_TOKEN;

    function peticion(url, datos) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Auth-Token': API_TOKEN,
                'X-CSRF-Token': CSRF_TOKEN,
            },
            body: new URLSearchParams(datos),
        }).then(r => r.json());
    }

    document.getElementById('btn-actualizar-perfil').addEventListener('click', async function () {
        const res = await peticion('/?c=Auth&m=actualizarPerfil', {
            nombre: document.getElementById('perfil-nombre').value,
            email: document.getElementById('perfil-email').value,
        });
        if (res.ok) {
            Swal.fire({ icon: 'success', title: res.mensaje, timer: 1500, showConfirmButton: false });
            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.error });
        }
    });

    document.getElementById('btn-cambiar-clave').addEventListener('click', async function () {
        const res = await peticion('/?c=Auth&m=cambiarPassword', {
            clave_actual: document.getElementById('clave-actual').value,
            clave_nueva: document.getElementById('clave-nueva').value,
            clave_confirm: document.getElementById('clave-confirm').value,
        });
        if (res.ok) {
            Swal.fire({ icon: 'success', title: res.mensaje, text: 'Tu sesión se mantiene activa.', timer: 2000, showConfirmButton: false });
            ['clave-actual', 'clave-nueva', 'clave-confirm'].forEach(id => document.getElementById(id).value = '');
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.error });
        }
    });
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
