<?php

require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/repositories/ResenaRepository.php';

class ProductoController
{
    private $repo;

    public function __construct()
    {
        $this->repo = new ProductoRepository();
    }

    public function index()
    {
        $busqueda = trim($_GET['q'] ?? '');
        $categoriaId = isset($_GET['categoria']) && $_GET['categoria'] !== '' ? (int)$_GET['categoria'] : null;
        $talla = trim($_GET['talla'] ?? '');
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));

        if ($categoriaId === 0) {
            $categoriaId = null;
        }

        $data = $this->repo->buscar($busqueda, $categoriaId, $pagina, 8, $talla !== '' ? $talla : null);
        $productos = $data['productos'];
        $total = $data['total'];
        $paginas = $data['paginas'];
        $pagina = $data['pagina'];
        $categorias = $this->repo->categorias();

        require BASE_PATH . '/views/productos/index.php';
    }

    public function ver()
    {
        $producto = $this->repo->find((int)($_GET['id'] ?? 0));

        if (!$producto || (int)$producto['activo'] !== 1) {
            header('Location: /?c=Producto&m=index');
            exit;
        }

        $producto['tallas'] = $this->repo->tallas((int)$producto['id']);

        $repoResenas = new ResenaRepository();
        $resenas = $repoResenas->porProducto((int)$producto['id']);
        $promedio = $repoResenas->promedio((int)$producto['id']);

        $puedeReseniar = false;
        $yaResenio = false;
        if (Auth::check()) {
            $puedeReseniar = $repoResenas->compro(Auth::id(), (int)$producto['id']);
            $yaResenio = $repoResenas->yaResenio(Auth::id(), (int)$producto['id']);
        }

        require BASE_PATH . '/views/productos/ver.php';
    }
}
