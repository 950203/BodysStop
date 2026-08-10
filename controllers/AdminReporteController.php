<?php

require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Security.php';
require_once BASE_PATH . '/repositories/PedidoRepository.php';

class AdminReporteController
{
    private PedidoRepository $repo;

    public function __construct()
    {
        $this->repo = new PedidoRepository();
    }

    // Reportes de ventas con filtro por rango de fechas
    public function index()
    {
        Auth::requireLogin([Auth::ROL_ADMIN]);

        $desde = $this->validarFecha($_GET['desde'] ?? '');
        $hasta = $this->validarFecha($_GET['hasta'] ?? '');

        $resumen = $this->repo->reporteResumen($desde, $hasta);
        $porDia = $this->repo->reporteVentasPorDia($desde, $hasta);
        $porProducto = $this->repo->reporteVentasPorProducto($desde, $hasta);
        $porCategoria = $this->repo->reporteVentasPorCategoria($desde, $hasta);
        $porMetodo = $this->repo->reporteVentasPorMetodo($desde, $hasta);
        $porEstado = $this->repo->reporteVentasPorEstado($desde, $hasta);
        $topClientes = $this->repo->reporteTopClientes($desde, $hasta);

        $ticketPromedio = $resumen['pedidos_validos'] > 0
            ? $resumen['ingresos'] / $resumen['pedidos_validos']
            : 0;

        // Serie diaria completa (rellena con 0 los días sin ventas)
        $dias = [];
        $desdeSerie = $desde ?? date('Y-m-d', strtotime('-29 days'));
        $hastaSerie = $hasta ?? date('Y-m-d');

        $mapa = [];
        foreach ($porDia as $d) {
            $mapa[$d['fecha']] = $d;
        }
        for ($t = strtotime($desdeSerie); $t <= strtotime($hastaSerie); $t += 86400) {
            $f = date('Y-m-d', $t);
            $dias[] = [
                'fecha' => $f,
                'etiqueta' => date('d/m', $t),
                'ingresos' => (float)($mapa[$f]['ingresos'] ?? 0),
                'pedidos' => (int)($mapa[$f]['pedidos'] ?? 0),
            ];
        }

        require BASE_PATH . '/views/admin/reportes/index.php';
    }

    private function validarFecha(string $f): ?string
    {
        $f = trim($f);
        if ($f === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)) {
            return null;
        }
        $t = strtotime($f);
        if ($t === false) {
            return null;
        }
        return date('Y-m-d', $t);
    }
}
