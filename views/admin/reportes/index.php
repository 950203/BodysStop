<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<?php
$paleta = ['#212529', '#0d6efd', '#dc3545', '#198754', '#ffc107', '#6c757d', '#0dcaf0', '#6610f2'];

$estadosMap = [
    'pendiente' => ['label' => 'Pendiente', 'badge' => 'bg-warning text-dark'],
    'pagado'    => ['label' => 'Pagado',    'badge' => 'bg-success'],
    'en_camino' => ['label' => 'En camino', 'badge' => 'bg-primary'],
    'entregado' => ['label' => 'Entregado', 'badge' => 'bg-secondary'],
    'cancelado' => ['label' => 'Cancelado', 'badge' => 'bg-danger'],
];

$metodoLabels = ['nequi' => 'Nequi', 'daviplata' => 'Daviplata'];

$hayDatos = ($resumen['total_pedidos'] ?? 0) > 0;

$diasLabels = array_column($dias, 'etiqueta');
$diasIngresos = array_column($dias, 'ingresos');
$diasPedidos = array_column($dias, 'pedidos');

$catLabels = array_column($porCategoria, 'categoria');
$catIngresos = array_column($porCategoria, 'ingresos');
$catColores = array_map(fn($i) => $paleta[$i % count($paleta)], array_keys($catLabels));

$metIngresos = [];
$metLabels = [];
foreach ($porMetodo as $m) {
    $metLabels[] = $metodoLabels[$m['metodo_pago']] ?? ucfirst($m['metodo_pago']);
    $metIngresos[] = (float)$m['ingresos'];
}
$metColores = array_map(fn($i) => $paleta[$i % count($paleta)], array_keys($metLabels));

$totalCatIngresos = array_sum(array_map('floatval', $catIngresos));
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="fw-light mb-0" style="letter-spacing:1px;">Reportes</h1>
        <div>
            <a href="/?c=AdminPedido&m=index" class="btn btn-outline-dark btn-sm"><i class="fas fa-box me-1"></i> Pedidos</a>
            <span class="badge bg-dark ms-2"><i class="fas fa-chart-line me-1"></i> <?= Auth::rol() === Auth::ROL_ADMIN ? 'Administrador' : 'Vendedor' ?></span>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="get">
                <input type="hidden" name="c" value="AdminReporte">
                <input type="hidden" name="m" value="index">
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1">Desde</label>
                    <input type="date" name="desde" class="form-control" value="<?= $desde ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1">Hasta</label>
                    <input type="date" name="hasta" class="form-control" value="<?= $hasta ?>">
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100"><i class="fas fa-filter me-1"></i> Filtrar</button>
                    <?php if ($desde || $hasta): ?>
                        <a href="/?c=AdminReporte&m=index" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
                <div class="col-6 col-md-3 text-md-end small text-muted">
                    <?php if ($desde || $hasta): ?>
                        Del <?= date('d/m/Y', strtotime($desde ?? '1970-01-01')) ?> al <?= date('d/m/Y', strtotime($hasta ?? date('Y-m-d'))) ?>
                    <?php else: ?>
                        Últimos 30 días
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="text-muted small">Ingresos</div>
                <div class="display-6 fw-bold">$<?= number_format($resumen['ingresos']) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="text-muted small">Pedidos</div>
                <div class="display-6 fw-bold"><?= number_format($resumen['total_pedidos']) ?></div>
                <div class="small text-muted"><?= $resumen['cancelados'] ?> cancelados</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="text-muted small">Unidades vendidas</div>
                <div class="display-6 fw-bold"><?= number_format($resumen['unidades']) ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="text-muted small">Ticket promedio</div>
                <div class="display-6 fw-bold">$<?= number_format($ticketPromedio) ?></div>
            </div>
        </div>
    </div>

    <?php if (!$hayDatos): ?>
        <div class="text-center py-5">
            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
            <p class="text-muted">Todavía no hay ventas en este periodo.</p>
        </div>
    <?php else: ?>

        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white"><strong><i class="fas fa-calendar-week me-2"></i> Ingresos y pedidos por día</strong></div>
                    <div class="card-body">
                        <canvas id="chartDias" height="90"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white"><strong><i class="fas fa-wallet me-2"></i> Ingresos por método de pago</strong></div>
                    <div class="card-body">
                        <?php if (empty($metLabels)): ?>
                            <p class="text-muted text-center py-4 mb-0">Sin datos.</p>
                        <?php else: ?>
                            <canvas id="chartMetodo" height="200"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white"><strong><i class="fas fa-tags me-2"></i> Ventas por categoría</strong></div>
                    <div class="card-body table-responsive">
                        <?php if (empty($porCategoria)): ?>
                            <p class="text-muted text-center py-4 mb-0">Sin datos.</p>
                        <?php else: ?>
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                    <?php foreach ($porCategoria as $i => $c): ?>
                                        <?php $pct = $totalCatIngresos > 0 ? round((float)$c['ingresos'] / $totalCatIngresos * 100) : 0; ?>
                                        <tr>
                                            <td class="fw-semibold"><?= Security::escape($c['categoria']) ?></td>
                                            <td style="width:40%;">
                                                <div class="progress" style="height:8px;">
                                                    <div class="progress-bar" style="width:<?= $pct ?>%;background-color:<?= $catColores[$i] ?>;"></div>
                                                </div>
                                            </td>
                                            <td class="text-end text-muted small"><?= number_format($c['unidades']) ?> uds</td>
                                            <td class="text-end fw-semibold">$<?= number_format($c['ingresos']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white"><strong><i class="fas fa-clipboard-list me-2"></i> Pedidos por estado</strong></div>
                    <div class="card-body table-responsive">
                        <?php if (empty($porEstado)): ?>
                            <p class="text-muted text-center py-4 mb-0">Sin datos.</p>
                        <?php else: ?>
                            <?php $totalEstado = array_sum(array_column($porEstado, 'pedidos')); ?>
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                    <?php foreach ($porEstado as $e): ?>
                                        <?php $eConf = $estadosMap[$e['estado']] ?? ['label' => ucfirst($e['estado']), 'badge' => 'bg-secondary']; ?>
                                        <?php $pct = $totalEstado > 0 ? round((int)$e['pedidos'] / $totalEstado * 100) : 0; ?>
                                        <tr>
                                            <td><span class="badge <?= $eConf['badge'] ?>"><?= $eConf['label'] ?></span></td>
                                            <td style="width:35%;">
                                                <div class="progress" style="height:8px;">
                                                    <div class="progress-bar <?= $eConf['badge'] === 'bg-warning text-dark' ? 'bg-warning' : $eConf['badge'] ?>" style="width:<?= $pct ?>%;"></div>
                                                </div>
                                            </td>
                                            <td class="text-end text-muted small"><?= $e['pedidos'] ?> (<?= $pct ?>%)</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white"><strong><i class="fas fa-fire me-2"></i> Productos más vendidos</strong></div>
                    <div class="card-body table-responsive">
                        <?php if (empty($porProducto)): ?>
                            <p class="text-muted text-center py-4 mb-0">Sin datos.</p>
                        <?php else: ?>
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Unidades</th>
                                        <th class="text-end">Ingresos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($porProducto as $p): ?>
                                        <tr>
                                            <td>
                                                <img src="<?= Security::escape($p['imagen']) ?>" alt="" style="width:32px;height:32px;object-fit:cover;border-radius:6px;" class="me-2">
                                                <?= Security::escape($p['nombre']) ?>
                                            </td>
                                            <td><?= number_format($p['unidades']) ?></td>
                                            <td class="text-end fw-semibold">$<?= number_format($p['ingresos']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white"><strong><i class="fas fa-trophy me-2"></i> Mejores clientes</strong></div>
                    <div class="card-body table-responsive">
                        <?php if (empty($topClientes)): ?>
                            <p class="text-muted text-center py-4 mb-0">Sin datos.</p>
                        <?php else: ?>
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Pedidos</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topClientes as $c): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?= Security::escape($c['nombre']) ?></div>
                                                <small class="text-muted"><?= Security::escape($c['email']) ?></small>
                                            </td>
                                            <td><?= number_format($c['pedidos']) ?></td>
                                            <td class="text-end fw-semibold">$<?= number_format($c['total']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
<?php if ($hayDatos): ?>
    const diasLabels = <?= json_encode($diasLabels) ?>;
    const diasIngresos = <?= json_encode($diasIngresos) ?>;
    const diasPedidos = <?= json_encode($diasPedidos) ?>;

    new Chart(document.getElementById('chartDias'), {
        data: {
            labels: diasLabels,
            datasets: [
                {
                    type: 'line',
                    label: 'Ingresos',
                    data: diasIngresos,
                    yAxisID: 'y',
                    borderColor: '#212529',
                    backgroundColor: 'rgba(33, 37, 41, 0.08)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 2,
                },
                {
                    type: 'bar',
                    label: 'Pedidos',
                    data: diasPedidos,
                    yAxisID: 'y1',
                    backgroundColor: 'rgba(173, 181, 189, 0.6)',
                    borderRadius: 3,
                },
            ],
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
            },
        },
    });

    <?php if (!empty($metLabels)): ?>
    new Chart(document.getElementById('chartMetodo'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($metLabels) ?>,
            datasets: [{
                data: <?= json_encode($metIngresos) ?>,
                backgroundColor: <?= json_encode($metColores) ?>,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
        },
    });
    <?php endif; ?>
<?php endif; ?>
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
