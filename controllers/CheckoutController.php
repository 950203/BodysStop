<?php

require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Security.php';
require_once BASE_PATH . '/repositories/PedidoRepository.php';
require_once BASE_PATH . '/repositories/ProductoRepository.php';
require_once BASE_PATH . '/repositories/CarritoRepository.php';

class CheckoutController
{
    public function __construct()
    {
        Auth::requireLogin([Auth::ROL_USUARIO]);
    }

    public function index()
    {
        if (empty($_SESSION['cart'])) {
            header('Location: /');
            exit;
        }

        require BASE_PATH . '/views/checkout/index.php';
    }

    public function process()
    {
        Auth::requireToken();
        Security::requireCsrf();

        if (empty($_SESSION['cart'])) {
            echo json_encode(['ok' => false, 'error' => 'Carrito vacío']);
            exit;
        }

        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $metodoPago = $_POST['metodo_pago'] ?? '';

        if (!$nombre || !$email || !$direccion) {
            echo json_encode(['ok' => false, 'error' => 'Completa todos los campos']);
            exit;
        }

        if (!in_array($metodoPago, ['nequi', 'daviplata'], true)) {
            echo json_encode(['ok' => false, 'error' => 'Selecciona un método de pago válido']);
            exit;
        }

        $repo = new PedidoRepository();
        $repoProductos = new ProductoRepository();
        $cart = [];

        foreach ($_SESSION['cart'] as $clave => $qty) {
            [$id, $talla] = array_pad(explode(':', $clave, 2), 2, '');
            $producto = $repoProductos->find((int)$id);
            if ($producto) {
                $producto['talla'] = $talla;
                $producto['qty'] = (int)$qty;
                $producto['subtotal'] = (int)$qty * $producto['precio'];
                $cart[] = $producto;
            }
        }

        $resultado = $repo->guardarPedido([
            'nombre' => $nombre,
            'email' => $email,
            'direccion' => $direccion,
            'metodo_pago' => $metodoPago
        ], $cart, Auth::id());

        if ($resultado['ok']) {
            $_SESSION['cart'] = [];
            (new CarritoRepository())->vaciar((int)Auth::id());
            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['ok' => false, 'error' => $resultado['error'] ?? 'Error al guardar el pedido']);
        }
    }
}
