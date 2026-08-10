<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-light mb-0" style="letter-spacing:1px;">Dashboard</h1>
        <div>
            <a href="/?c=AdminPedido&m=index" class="btn btn-outline-dark btn-sm"><i class="fas fa-box me-1"></i> Pedidos</a>
            <span class="badge bg-dark ms-2"><i class="fas fa-chart-line me-1"></i> <?= Auth::rol() === Auth::ROL_ADMIN ? 'Administrador' : 'Vendedor' ?></span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="text-muted small">Total pedidos</div>
                <div class="display-6 fw-bold"><?= number_format($metricas['total_pedidos']) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="text-muted small">Ingresos</div>
                <div class="display-6 fw-bold">$<?= number_format($metricas['ingresos']) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="text-muted small">Pedidos completados</div>
                <div class="display-6 fw-bold"><?= number_format($metricas['completados']) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="text-muted small">Clientes</div>
                <div class="display-6 fw-bold"><?= number_format($metricas['clientes']) ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><strong><i class="fas fa-chart-bar me-2"></i> Pedidos por estado</strong></div>
                <div class="card-body">
                    <?php $colores = ['pendiente' => 'warning', 'pagado' => 'success', 'en_camino' => 'primary', 'entregado' => 'secondary', 'cancelado' => 'danger']; ?>
                    <?php if (empty($metricas['por_estado'])): ?>
                        <p class="text-muted text-center py-4 mb-0">Sin datos todavía.</p>
                    <?php else: ?>
                        <?php $total = array_sum(array_column($metricas['por_estado'], 'cantidad')); ?>
                        <?php foreach ($metricas['por_estado'] as $fila): ?>
                            <?php $pct = $total > 0 ? round($fila['cantidad'] / $total * 100) : 0; ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span><?= $fila['estado'] === 'en_camino' ? 'En camino' : ucfirst($fila['estado']) ?></span>
                                    <span class="text-muted"><?= $fila['cantidad'] ?> (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar bg-<?= $colores[$fila['estado']] ?? 'dark' ?>" style="width:<?= $pct ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><strong><i class="fas fa-fire me-2"></i> Productos más vendidos</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Unidades</th>
                                <th class="text-end">Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($masVendidos)): ?>
                                <tr><td colspan="3" class="text-center text-muted py-4">Sin ventas todavía.</td></tr>
                            <?php else: ?>
                                <?php foreach ($masVendidos as $p): ?>
                                    <tr>
                                        <td>
                                            <img src="<?= Security::escape($p['imagen']) ?>" alt="" style="width:36px;height:36px;object-fit:cover;border-radius:6px;" class="me-2">
                                            <?= Security::escape($p['nombre']) ?>
                                        </td>
                                        <td><?= $p['vendidos'] ?></td>
                                        <td class="text-end">$<?= number_format($p['precio']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><strong><i class="fas fa-users me-2"></i> Usuarios recientes</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <tbody>
                            <?php if (empty($usuariosRecientes)): ?>
                                <tr><td class="text-center text-muted py-4">Sin usuarios.</td></tr>
                            <?php else: ?>
                                <?php foreach ($usuariosRecientes as $u): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= Security::escape($u['nombre']) ?></td>
                                        <td class="small text-muted"><?= Security::escape($u['email']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= Security::escape($u['rol']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
