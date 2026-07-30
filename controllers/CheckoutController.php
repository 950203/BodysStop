<?php

class CheckoutController
{
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
        if (empty($_SESSION['cart'])) {
            echo json_encode(['ok' => false, 'error' => 'Carrito vacío']);
            exit;
        }

        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $direccion = $_POST['direccion'] ?? '';

        if (!$nombre || !$email || !$direccion) {
            echo json_encode(['ok' => false, 'error' => 'Completa todos los campos']);
            exit;
        }

        $repo = new PedidoRepository();
        $cart = [];

        foreach ($_SESSION['cart'] as $id => $qty) {
            $producto = (new ProductoRepository())->find($id);
            if ($producto) {
                $producto['qty'] = $qty;
                $producto['subtotal'] = $qty * $producto['precio'];
                $cart[] = $producto;
            }
        }

        $ok = $repo->guardarPedido([
            'nombre' => $nombre,
            'email' => $email,
            'direccion' => $direccion
        ], $cart);

        if ($ok) {
            $_SESSION['cart'] = [];
            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'Error al guardar el pedido']);
        }
    }
}
