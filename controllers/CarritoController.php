<?php

require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Security.php';
require_once BASE_PATH . '/repositories/ProductoRepository.php';
require_once BASE_PATH . '/repositories/CarritoRepository.php';

class CarritoController
{
    private ProductoRepository $repo;
    private CarritoRepository $repoBD;

    public function __construct()
    {
        Auth::requireLogin([Auth::ROL_USUARIO]);

        $this->repo = new ProductoRepository();
        $this->repoBD = new CarritoRepository();
        $_SESSION['cart'] = $_SESSION['cart'] ?? [];
    }

    // Guarda el carrito de la sesión en la BD para que persista entre sesiones
    private function sincronizarBD(): void
    {
        if (Auth::check()) {
            $this->repoBD->guardar((int)Auth::id(), $_SESSION['cart'] ?? []);
        }
    }

    // Agrega un producto con su talla al carrito
    public function agregar()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $talla = trim($_POST['talla'] ?? '');
        $qty = max(1, (int)($_POST['qty'] ?? 1));

        if (!$this->repo->find($id)) {
            echo json_encode(['ok' => false, 'error' => 'Producto no encontrado']);
            exit;
        }

        $stock = $this->repo->stockDe($id, $talla);
        if ($talla === '' || $stock <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Talla no disponible']);
            exit;
        }

        $clave = "$id:$talla";
        $actual = $_SESSION['cart'][$clave] ?? 0;

        if ($actual + $qty > $stock) {
            echo json_encode(['ok' => false, 'error' => 'Solo hay ' . $stock . ' unidades en esa talla']);
            exit;
        }

        $_SESSION['cart'][$clave] = $actual + $qty;
        $this->sincronizarBD();
        echo json_encode(['ok' => true, 'total_items' => array_sum($_SESSION['cart'])]);
    }

    public function index()
    {
        $items = $this->items();
        $total = array_reduce($items, fn($acc, $i) => $acc + $i['subtotal'], 0);
        require BASE_PATH . '/views/carrito/index.php';
    }

    public function remove()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $clave = $_POST['id'] ?? '';
        unset($_SESSION['cart'][$clave]);
        $this->sincronizarBD();
        echo json_encode(['ok' => true]);
    }

    public function clear()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $_SESSION['cart'] = [];
        $this->sincronizarBD();
        echo json_encode(['ok' => true]);
    }

    public function count()
    {
        echo array_sum($_SESSION['cart'] ?? []);
    }

    public function sumar()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $clave = $_POST['id'] ?? '';
        if (isset($_SESSION['cart'][$clave])) {
            $this->incrementar($clave, 1);
        }

        $this->sincronizarBD();
        echo 'ok';
    }

    public function restar()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $clave = $_POST['id'] ?? '';
        if (isset($_SESSION['cart'][$clave])) {
            $this->incrementar($clave, -1);
        }

        $this->sincronizarBD();
        echo 'ok';
    }

    public function mini()
    {
        $items = $this->items();
        $total = array_reduce($items, fn($acc, $i) => $acc + $i['subtotal'], 0);
        require BASE_PATH . '/views/carrito/mini.php';
    }

    public function updateQty()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $clave = $_POST['id'] ?? '';
        $qty = (int)$_POST['qty'] ?? 0;

        if ($qty <= 0) {
            unset($_SESSION['cart'][$clave]);
        } else {
            $this->incrementar($clave, $qty - (int)($_SESSION['cart'][$clave] ?? 0), true);
        }

        $this->sincronizarBD();
        echo json_encode(['ok' => true]);
    }

    private function incrementar(string $clave, int $delta, bool $absoluto = false)
    {
        [$id, $talla] = array_pad(explode(':', $clave, 2), 2, '');
        $id = (int)$id;

        $nuevo = $absoluto ? $delta : (int)($_SESSION['cart'][$clave] ?? 0) + $delta;
        $stock = $this->repo->stockDe($id, $talla);

        if ($nuevo > $stock) {
            $nuevo = max(1, $stock);
        }

        if ($nuevo <= 0) {
            unset($_SESSION['cart'][$clave]);
        } else {
            $_SESSION['cart'][$clave] = $nuevo;
        }
    }

    // Construye los items del carrito con su talla y subtotales
    private function items(): array
    {
        $items = [];

        foreach ($_SESSION['cart'] as $clave => $qty) {
            [$id, $talla] = array_pad(explode(':', $clave, 2), 2, '');
            $producto = $this->repo->find((int)$id);

            if ($producto) {
                $producto['clave'] = $clave;
                $producto['talla'] = $talla;
                $producto['qty'] = (int)$qty;
                $producto['subtotal'] = (int)$qty * $producto['precio'];
                $producto['stock'] = $this->repo->stockDe((int)$id, $talla);
                $items[] = $producto;
            }
        }

        return $items;
    }
}
