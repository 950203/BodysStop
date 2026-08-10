<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-light mb-0" style="letter-spacing:1px;">Historial de Ventas</h1>
        <div>
            <a href="/?c=AdminPedido&m=index" class="btn btn-outline-dark btn-sm"><i class="fas fa-box me-1"></i> Pedidos</a>
            <span class="badge bg-dark ms-2"><i class="fas fa-chart-line me-1"></i> <?= Auth::rol() === Auth::ROL_ADMIN ? 'Administrador' : 'Vendedor' ?></span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="fas fa-shopping-bag"></i></div>
                    <div>
                        <div class="text-muted small">Total de ventas</div>
                        <div class="fs-4 fw-bold"><?= $totalVentas ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="fas fa-dollar-sign"></i></div>
                    <div>
                        <div class="text-muted small">Ingresos totales</div>
                        <div class="fs-4 fw-bold">$<?= number_format($ingresos) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="fas fa-box-open"></i></div>
                    <div>
                        <div class="text-muted small">Unidades vendidas</div>
                        <div class="fs-4 fw-bold"><?= $unidades ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (Auth::rol() === Auth::ROL_ADMIN): ?>
        <form method="GET" class="mb-4">
            <input type="hidden" name="c" value="AdminPedido">
            <input type="hidden" name="m" value="ventas">
            <div class="col-md-4">
                <label class="form-label small text-muted">Filtrar por vendedor</label>
                <select name="vendedor" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos los vendedores</option>
                    <?php foreach ($vendedores as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= isset($vendedorFiltro) && $vendedorFiltro === (int)$v['id'] ? 'selected' : '' ?>>
                            <?= Security::escape(trim(($v['marca'] ?? '') . ($v['marca'] ? ' — ' : '') . $v['nombre'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    <?php endif; ?>

    <?php if (empty($ventas)): ?>
        <div class="text-center py-5">
            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
            <p class="text-muted">Todavía no hay ventas registradas.</p>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong><i class="fas fa-history me-2"></i> Ventas registradas</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th>Productos</th>
                            <th>Pago</th>
                            <th>Estado</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $v): ?>
                            <tr>
                                <td><strong>#<?= str_pad($v['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                                <td class="small"><?= date('d/m/Y H:i', strtotime($v['created_at'])) ?></td>
                                <td>
                                    <div class="fw-semibold"><?= Security::escape($v['nombre_cliente']) ?></div>
                                    <small class="text-muted"><?= Security::escape($v['email']) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($v['marcas'])): ?>
                                        <?php foreach ($v['marcas'] as $m): ?>
                                            <span class="badge bg-dark"><?= Security::escape($m) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php foreach ($v['detalle'] as $d): ?>
                                        <div class="small mb-1">
                                            <img src="<?= Security::escape($d['producto_imagen']) ?>" alt="" style="width:22px;height:22px;object-fit:cover;border-radius:4px;" class="me-1">
                                            <?= Security::escape($d['producto_nombre']) ?>
                                            <?php if (!empty($d['producto_marca'])): ?><span class="badge bg-light text-dark border"><?= Security::escape($d['producto_marca']) ?></span><?php endif; ?>
                                            <span class="badge bg-light text-dark border"><?= Security::escape($d['talla']) ?></span>
                                            <span class="text-muted">× <?= $d['cantidad'] ?></span>
                                            <span class="float-end text-muted">$<?= number_format($d['subtotal']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php if ($v['metodo_pago'] === 'nequi'): ?>
                                        <span class="badge bg-success">Nequi</span>
                                    <?php elseif ($v['metodo_pago'] === 'daviplata'): ?>
                                        <span class="badge bg-danger">Daviplata</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= match ($v['estado']) {
                                        'pagado' => 'bg-success',
                                        'en_camino' => 'bg-primary',
                                        'entregado' => 'bg-secondary',
                                        'cancelado' => 'bg-danger',
                                        default => 'bg-warning text-dark',
                                    } ?>"><?= $v['estado'] === 'en_camino' ? 'En camino' : Security::escape($v['estado']) ?></span>
                                </td>
                                <td class="text-end fw-bold">$<?= number_format($v['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" class="text-end fw-bold">Ingresos totales</td>
                            <td class="text-end fw-bold">$<?= number_format($ingresos) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
