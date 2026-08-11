<?php

require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Security.php';
require_once BASE_PATH . '/core/TokenService.php';
require_once BASE_PATH . '/repositories/PedidoRepository.php';
require_once BASE_PATH . '/repositories/UsuarioRepository.php';

class AdminPedidoController
{
    private PedidoRepository $repo;

    public function __construct()
    {
        $this->repo = new PedidoRepository();
    }

    // Listado de pedidos con filtros.
    // El administrador ve todos; el vendedor solo los que contienen su marca/productos.
    public function index()
    {
        Auth::requireLogin([Auth::ROL_VENDEDOR, Auth::ROL_ADMIN]);

        $estado = $_GET['estado'] ?? '';
        $busqueda = trim($_GET['busqueda'] ?? '');
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));

        $vendedorId = Auth::rol() === Auth::ROL_ADMIN ? null : Auth::id();

        $data = $this->repo->todos($estado, $busqueda, $pagina, 10, $vendedorId);
        $pedidos = $data['pedidos'];
        $total = $data['total'];
        $paginas = $data['paginas'];
        $pagina = $data['pagina'];
        $estados = ['pendiente', 'pagado', 'en_camino', 'entregado', 'cancelado'];

        require BASE_PATH . '/views/admin/pedidos/index.php';
    }

    // Detalle de un pedido
    public function ver()
    {
        Auth::requireLogin([Auth::ROL_VENDEDOR, Auth::ROL_ADMIN]);

        $pedido = $this->repo->find((int)($_GET['id'] ?? 0));
        if (!$pedido) {
            header('Location: /?c=AdminPedido&m=index&error=' . urlencode('Pedido no encontrado.'));
            exit;
        }

        // El vendedor solo ve sus propias marcas/líneas dentro del pedido
        if (Auth::rol() === Auth::ROL_VENDEDOR) {
            $pedido['detalle'] = array_values(array_filter(
                $pedido['detalle'],
                fn($d) => (int)$d['producto_vendedor_id'] === (int)Auth::id()
            ));
            $pedido['total'] = array_sum(array_map(fn($d) => (float)$d['subtotal'], $pedido['detalle']));
        }

        require BASE_PATH . '/views/admin/pedidos/ver.php';
    }

    // Cambio de estado (AJAX) — solo el administrador gestiona los estados
    public function cambiarEstado()
    {
        Auth::requireLogin([Auth::ROL_VENDEDOR, Auth::ROL_ADMIN]);
        Auth::requireToken();
        Security::requireCsrf();

        if (Auth::rol() !== Auth::ROL_ADMIN) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Solo el administrador puede gestionar los estados de los pedidos']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? '';
        $estadosValidos = ['pendiente', 'pagado', 'en_camino', 'entregado', 'cancelado'];

        if (!in_array($estado, $estadosValidos, true)) {
            echo json_encode(['ok' => false, 'error' => 'Estado inválido']);
            exit;
        }

        if ($this->repo->cambiarEstado($id, $estado)) {
            echo json_encode(['ok' => true, 'mensaje' => 'Estado actualizado']);
        } else {
            echo json_encode(['ok' => false, 'error' => 'No se pudo actualizar el estado']);
        }
    }

    // Historial de ventas con resumen.
    // El administrador puede filtrar por vendedor; el vendedor solo ve sus propias ventas.
    public function ventas()
    {
        Auth::requireLogin([Auth::ROL_VENDEDOR, Auth::ROL_ADMIN]);

        $vendedores = (new UsuarioRepository())->vendedores();

        if (Auth::rol() === Auth::ROL_ADMIN) {
            $vendedorFiltro = ($_GET['vendedor'] ?? '') !== '' ? (int)$_GET['vendedor'] : null;
        } else {
            $vendedorFiltro = Auth::id();
        }

        $ventas = $this->repo->historialVentas($vendedorFiltro);

        $totalVentas = count($ventas);
        $ingresos = array_sum(array_map(
            fn($p) => array_sum(array_map(fn($d) => (float)$d['subtotal'], $p['detalle'])),
            $ventas
        ));
        $unidades = array_sum(array_map(
            fn($p) => array_sum(array_map(fn($d) => (int)$d['cantidad'], $p['detalle'])),
            $ventas
        ));

        require BASE_PATH . '/views/admin/pedidos/ventas.php';
    }

    // Dashboard de métricas (solo administrador)
    public function dashboard()
    {
        Auth::requireLogin([Auth::ROL_ADMIN]);

        $metricas = $this->repo->metricas();
        $masVendidos = $this->repo->masVendidos();
        $usuariosRecientes = (new UsuarioRepository())->all();
        $usuariosRecientes = array_slice($usuariosRecientes, 0, 5);

        require BASE_PATH . '/views/admin/dashboard.php';
    }
}
