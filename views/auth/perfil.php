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

    <?php
        $pasosSeguimiento = [
            ['clave' => 'pendiente', 'titulo' => 'Realizado', 'icono' => 'fa-box'],
            ['clave' => 'pagado',    'titulo' => 'Pagado',    'icono' => 'fa-credit-card'],
            ['clave' => 'en_camino', 'titulo' => 'En camino', 'icono' => 'fa-truck'],
            ['clave' => 'entregado', 'titulo' => 'Entregado', 'icono' => 'fa-home'],
        ];
        $indiceEstado = ['pendiente' => 0, 'pagado' => 1, 'en_camino' => 2, 'entregado' => 3];

        $etiquetaEstado = fn($e) => match ($e) { 'en_camino' => 'En camino', 'pagado' => 'Pagado', 'entregado' => 'Entregado', 'cancelado' => 'Cancelado', default => 'Pendiente' };

        $pedidoActivo = null;
        foreach ($pedidos as $pedido) {
            if (in_array($pedido['estado'], ['en_camino', 'pagado', 'pendiente'], true)) {
                $pedidoActivo = $pedido;
                break;
            }
        }
    ?>

    <h2 class="fw-light my-4" style="letter-spacing:1px;">Mis pedidos</h2>

    <?php if ($pedidoActivo): ?>
        <?php
            $estimadaAct = !empty($pedidoActivo['fecha_estimada_entrega']) ? strtotime($pedidoActivo['fecha_estimada_entrega']) : null;
            $retrasoAct = $estimadaAct !== null && $estimadaAct < time();
            $numPedido = str_pad($pedidoActivo['id'], 5, '0', STR_PAD_LEFT);
        ?>
        <div class="alert <?= $retrasoAct ? 'bg-danger' : 'bg-dark' ?> text-white border-0 shadow-sm d-flex justify-content-between align-items-center gap-3">
            <div>
                <i class="fas <?= $pedidoActivo['estado'] === 'en_camino' ? 'fa-truck-fast' : ($pedidoActivo['estado'] === 'pagado' ? 'fa-box' : 'fa-clock') ?> me-2"></i>
                <?php if ($retrasoAct): ?>
                    <strong>Tu pedido #<?= $numPedido ?> presenta un retraso en la entrega.</strong>
                    <span class="d-block small opacity-75">Sigue avanzando; te notificaremos cuando esté en reparto.</span>
                <?php else: ?>
                    <strong><?= match ($pedidoActivo['estado']) {
                        'en_camino' => 'Tu pedido #' . $numPedido . ' va en camino',
                        'pagado' => 'Tu pedido #' . $numPedido . ' está en preparación',
                        default => 'Tu pedido #' . $numPedido . ' está pendiente de pago',
                    } ?></strong>
                    <span class="d-block small opacity-75">
                        Entrega estimada: <?= $estimadaAct !== null ? date('d M, H:i', $estimadaAct) : 'por confirmar' ?>
                        <?php if ($estimadaAct !== null): ?>
                            · <span data-entrega="<?= $estimadaAct ?>">calculando…</span>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
            <button class="btn btn-sm btn-light text-dark flex-shrink-0" onclick="document.getElementById('pedido-<?= $pedidoActivo['id'] ?>').scrollIntoView({behavior:'smooth'}); setTimeout(() => toggleSeguimiento(<?= $pedidoActivo['id'] ?>), 400);">
                <i class="fas fa-location-dot me-1"></i> Ver seguimiento
            </button>
        </div>
    <?php endif; ?>

    <?php if (empty($pedidos)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-box-open fa-3x mb-3"></i>
            <p>Aún no tienes pedidos.</p>
            <a href="/?c=Producto&m=index" class="btn btn-dark">Ir a la tienda</a>
        </div>
    <?php else: ?>
        <?php foreach ($pedidos as $pedido): ?>
            <?php
                $estadoPedido = $pedido['estado'];
                $estimada = !empty($pedido['fecha_estimada_entrega']) ? strtotime($pedido['fecha_estimada_entrega']) : null;
                $activoPedido = in_array($estadoPedido, ['pendiente', 'pagado', 'en_camino'], true);
                $retraso = $activoPedido && $estimada !== null && $estimada < time();
                $cancelado = $estadoPedido === 'cancelado';
                $pasoActual = $indiceEstado[$estadoPedido] ?? -1;
            ?>
            <div class="card border-0 shadow-sm mb-3" id="pedido-<?= $pedido['id'] ?>">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span><strong>Pedido #<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></strong></span>
                    <span class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge <?= match ($pedido['metodo_pago']) {
                            'nequi' => 'bg-success',
                            'daviplata' => 'bg-danger',
                            default => 'bg-secondary',
                        } ?>"><?= Security::escape($pedido['metodo_pago'] ?? 'Sin pago') ?></span>
                        <span class="badge <?= match ($estadoPedido) {
                            'pagado' => 'bg-success',
                            'en_camino' => 'bg-primary',
                            'entregado' => 'bg-secondary',
                            'cancelado' => 'bg-danger',
                            default => 'bg-warning text-dark',
                        } ?>"><?= $etiquetaEstado($estadoPedido) ?></span>
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
                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="small">
                            <?php if ($estimada !== null): ?>
                                <?php if ($cancelado): ?>
                                    <span class="text-muted"><i class="fas fa-ban me-1"></i>Pedido cancelado</span>
                                <?php elseif ($estadoPedido === 'entregado'): ?>
                                    <span class="text-success fw-semibold"><i class="fas fa-check-circle me-1"></i>Entregado</span>
                                <?php elseif ($retraso): ?>
                                    <span class="text-danger fw-semibold"><i class="fas fa-exclamation-triangle me-1"></i>Posible retraso en la entrega</span>
                                <?php else: ?>
                                    <span class="text-muted"><i class="fas fa-truck-fast me-1"></i>Entrega estimada: <strong><?= date('d M, H:i', $estimada) ?></strong></span>
                                    <span class="text-muted" data-entrega="<?= $estimada ?>">calculando…</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Sin fecha estimada.</span>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-outline-dark btn-sm" onclick="toggleSeguimiento(<?= $pedido['id'] ?>)">
                            <i class="fas fa-location-dot me-1"></i> Rastrear pedido
                        </button>
                    </div>

                    <div id="seguimiento-<?= $pedido['id'] ?>" class="d-none mt-3 pt-3 border-top">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="small text-muted mb-2"><i class="fas fa-route me-1"></i> Ruta del envío</div>
                                <div class="d-flex flex-column gap-1">
                                    <div class="p-2 border rounded small">
                                        <i class="fas fa-warehouse me-1 text-dark"></i><strong>Desde:</strong> Medellín, Colombia
                                    </div>
                                    <div class="text-center text-muted"><i class="fas fa-arrow-down"></i></div>
                                    <div class="p-2 border rounded small">
                                        <i class="fas fa-home me-1 text-dark"></i><strong>Hasta:</strong>
                                        <?= $pedido['direccion'] ? Security::escape($pedido['direccion']) : 'Tu dirección registrada' ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="small text-muted mb-2"><i class="fas fa-map-pin me-1"></i> Estado del envío</div>
                                <?php if (!$cancelado): ?>
                                    <div class="d-flex justify-content-between mb-3">
                                        <?php foreach ($pasosSeguimiento as $i => $paso): ?>
                                            <?php $hecho = $i <= $pasoActual; ?>
                                            <div class="text-center flex-fill">
                                                <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center <?= $hecho ? 'bg-dark text-white' : 'bg-light text-muted border' ?>" style="width:42px;height:42px;">
                                                    <i class="fas <?= $paso['icono'] ?>"></i>
                                                </div>
                                                <small class="d-block mt-1 <?= $hecho ? 'fw-semibold text-dark' : 'text-muted' ?>"><?= $paso['titulo'] ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-danger py-2 small">Este pedido fue cancelado y no continuará con la entrega.</div>
                                <?php endif; ?>

                                <div class="small text-muted mb-2"><i class="fas fa-history me-1"></i> Historial de seguimiento</div>
                                <?php if (empty($pedido['seguimiento'])): ?>
                                    <div class="small text-muted">No hay información de seguimiento para este pedido.</div>
                                <?php else: ?>
                                    <?php foreach ($pedido['seguimiento'] as $ev): ?>
                                        <div class="d-flex gap-3 mb-3">
                                            <div class="text-center" style="width:16px;">
                                                <i class="fas fa-circle text-dark" style="font-size:10px;margin-top:6px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold"><?= Security::escape($ev['titulo']) ?></div>
                                                <div class="small text-muted">
                                                    <?= date('d M, H:i', strtotime($ev['created_at'])) ?>
                                                    <?= $ev['ubicacion'] ? ' · ' . Security::escape($ev['ubicacion']) : '' ?>
                                                </div>
                                                <?php if (!empty($ev['detalle'])): ?>
                                                    <div class="small"><?= Security::escape($ev['detalle']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
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

    function toggleSeguimiento(id) {
        const el = document.getElementById('seguimiento-' + id);
        if (el) el.classList.toggle('d-none');
    }

    function formatearRestante(seg) {
        const d = Math.floor(seg / 86400);
        const h = Math.floor((seg % 86400) / 3600);
        const m = Math.floor((seg % 3600) / 60);
        if (d > 0) return d + (d === 1 ? ' día y ' : ' días y ') + h + ' h';
        if (h > 0) return h + ' h ' + m + ' min';
        return m + ' min';
    }

    function iniciarCuentasRegresivas() {
        document.querySelectorAll('[data-entrega]').forEach(el => {
            const target = parseInt(el.dataset.entrega, 10) * 1000;
            if (isNaN(target)) return;
            const tick = () => {
                const diff = target - Date.now();
                if (diff <= 0) {
                    el.textContent = 'entregándose…';
                    clearInterval(el._intervalo);
                    return;
                }
                el.textContent = 'faltan ' + formatearRestante(Math.floor(diff / 1000));
            };
            tick();
            el._intervalo = setInterval(tick, 1000);
        });
    }

    document.addEventListener('DOMContentLoaded', iniciarCuentasRegresivas);
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
