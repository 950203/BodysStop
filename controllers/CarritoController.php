<?php

class CarritoController
{
    private $repo;

    public function __construct()
    {
        $this->repo = new ProductoRepository();
        $_SESSION['cart'] = $_SESSION['cart'] ?? [];
    }

    public function agregar()
    {
        $id = $_POST['id'];

        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;

        echo json_encode(['ok' => true]);
    }

    public function index()
    {
        $items = [];
        $total = 0;

        foreach ($_SESSION['cart'] as $id => $qty) {
            $producto = $this->repo->find($id);
            if ($producto) {
                $producto['qty'] = $qty;
                $producto['subtotal'] = $qty * $producto['precio'];
                $total += $producto['subtotal'];
                $items[] = $producto;
            }
        }

        require BASE_PATH . '/views/carrito/index.php';
    }

    public function remove()
    {
        $id = $_POST['id'];
        unset($_SESSION['cart'][$id]);
        echo json_encode(['ok' => true]);
    }

    public function clear()
    {
        $_SESSION['cart'] = [];
        echo json_encode(['ok' => true]);
    }

    public function count()
    {
        $count = 0;

        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $cantidad) {
                $count += (int)$cantidad;
            }
        }

        echo $count;
    }

    public function sumar()
    {
        $id = $_POST['id'];

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
        }

        echo 'ok';
    }

    public function restar()
    {
        $id = $_POST['id'];

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]--;

            if ($_SESSION['cart'][$id] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }

        echo 'ok';
    }

    public function mini()
    {
        $items = [];
        $total = 0;

        foreach ($_SESSION['cart'] ?? [] as $id => $qty) {
            $producto = $this->repo->find($id);
            if ($producto) {
                $producto['qty'] = $qty;
                $producto['subtotal'] = $qty * $producto['precio'];
                $total += $producto['subtotal'];
                $items[] = $producto;
            }
        }

        require BASE_PATH . '/views/carrito/mini.php';
    }

    public function updateQty()
    {
        $id = $_POST['id'];
        $qty = (int) $_POST['qty'];

        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id] = $qty;
        }

        echo json_encode(['ok' => true]);
    }
}