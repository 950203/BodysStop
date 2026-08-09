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

    // Listado de pedidos con filtros
    public function index()
    {
        Auth::requireLogin([Auth::ROL_VENDEDOR, Auth::ROL_ADMIN]);

        $estado = $_GET['estado'] ?? '';
        $busqueda = trim($_GET['busqueda'] ?? '');
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));

        $data = $this->repo->todos($estado, $busqueda, $pagina, 10);
        $pedidos = $data['pedidos'];
        $total = $data['total'];
        $paginas = $data['paginas'];
        $pagina = $data['pagina'];
        $estados = ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'];

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

        require BASE_PATH . '/views/admin/pedidos/ver.php';
    }

    // Cambio de estado (AJAX)
    public function cambiarEstado()
    {
        Auth::requireLogin([Auth::ROL_VENDEDOR, Auth::ROL_ADMIN]);
        Auth::requireToken();
        Security::requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? '';
        $estadosValidos = ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'];

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

    // Dashboard de métricas
    public function dashboard()
    {
        Auth::requireLogin([Auth::ROL_VENDEDOR, Auth::ROL_ADMIN]);

        $metricas = $this->repo->metricas();
        $masVendidos = $this->repo->masVendidos();
        $usuariosRecientes = (new UsuarioRepository())->all();
        $usuariosRecientes = array_slice($usuariosRecientes, 0, 5);

        require BASE_PATH . '/views/admin/dashboard.php';
    }
}
