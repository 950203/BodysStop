<?php

require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Security.php';

class AdminProductoController
{
    private $model;

    public function __construct()
    {
        Auth::requireLogin([Auth::ROL_VENDEDOR, Auth::ROL_ADMIN]);

        $this->model = new ProductoModel();
    }

    public function index()
    {
        Auth::requireLogin([Auth::ROL_ADMIN]);

        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $data = $this->model->allPag($pagina, 10);
        $productos = $data['productos'];
        $paginas = $data['paginas'];
        $pagina = $data['pagina'];
        require BASE_PATH . '/views/admin/productos/index.php';
    }

    // Página de stock por talla. El vendedor solo ve sus propios productos.
    public function stock()
    {
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));

        if (Auth::rol() === Auth::ROL_ADMIN) {
            $data = $this->model->allPag($pagina, 10);
        } else {
            $data = $this->model->allByVendedor((int)Auth::id(), $pagina, 10);
        }

        $productos = $data['productos'];
        $paginas = $data['paginas'];
        $pagina = $data['pagina'];
        require BASE_PATH . '/views/admin/stock/index.php';
    }

    public function create()
    {
        Auth::requireLogin([Auth::ROL_ADMIN]);

        $categorias = $this->model->categorias();
        $vendedores = $this->model->vendedores();
        require BASE_PATH . '/views/admin/productos/create.php';
    }

    public function store()
    {
        Auth::requireLogin([Auth::ROL_ADMIN]);
        Auth::requireToken();
        Security::requireCsrf();

        $imagen = $this->uploadImage();
        $marca = $this->marcaDesdePost();

        $id = $this->model->create([
            'nombre' => $_POST['nombre'],
            'descripcion' => $_POST['descripcion'] ?? '',
            'material' => $_POST['material'] ?? '',
            'precio' => $_POST['precio'],
            'imagen' => $imagen,
            'categoria_id' => $_POST['categoria_id'] ?? '',
            'marca' => $marca,
            'vendedor_id' => $_POST['vendedor_id'] ?? '',
        ]);

        if (Auth::rol() === Auth::ROL_ADMIN) {
            $this->model->guardarTallas((int)$id, $this->tallasDesdePost());
        }

        header('Location: /?c=AdminProducto&m=index');
    }

    public function edit()
    {
        Auth::requireLogin([Auth::ROL_ADMIN]);

        $producto = $this->model->find($_GET['id']);
        $categorias = $this->model->categorias();
        $vendedores = $this->model->vendedores();
        $tallas = $this->model->tallas((int)$producto['id']);
        require BASE_PATH . '/views/admin/productos/edit.php';
    }

    public function update()
    {
        Auth::requireLogin([Auth::ROL_ADMIN]);
        Auth::requireToken();
        Security::requireCsrf();

        $imagen = $_POST['imagen_actual'];

        if (!empty($_FILES['imagen']['name'])) {
            $imagen = $this->uploadImage();
        }

        $marca = $this->marcaDesdePost();

        $this->model->update($_POST['id'], [
            'nombre' => $_POST['nombre'],
            'descripcion' => $_POST['descripcion'] ?? '',
            'material' => $_POST['material'] ?? '',
            'precio' => $_POST['precio'],
            'imagen' => $imagen,
            'categoria_id' => $_POST['categoria_id'] ?? '',
            'marca' => $marca,
            'vendedor_id' => $_POST['vendedor_id'] ?? '',
        ]);

        if (Auth::rol() === Auth::ROL_ADMIN) {
            $this->model->guardarTallas((int)$_POST['id'], $this->tallasDesdePost());
        }

        header('Location: /?c=AdminProducto&m=index');
    }

    // Marca del producto: si se eligió vendedor y no se escribió marca, toma la del vendedor
    private function marcaDesdePost(): string
    {
        $marca = trim($_POST['marca'] ?? '');
        $vendedorId = trim($_POST['vendedor_id'] ?? '');
        if ($marca === '' && $vendedorId !== '') {
            $v = $this->model->vendedor((int)$vendedorId);
            $marca = $v['marca'] ?? '';
        }
        return $marca;
    }

    // Lee el arreglo "talla[talla]=stock" enviado desde el formulario
    private function tallasDesdePost(): array
    {
        $tallas = $_POST['talla'] ?? [];
        $resultado = [];

        foreach ($tallas as $talla => $stock) {
            if (trim($talla) !== '') {
                $resultado[$talla] = max(0, (int)$stock);
            }
        }

        return $resultado;
    }

    public function delete()
    {
        Auth::requireLogin([Auth::ROL_ADMIN]);
        Auth::requireToken();
        Security::requireCsrf();

        // Soft delete: oculta el producto de la tienda
        $this->model->delete($_POST['id']);
        echo json_encode(['ok' => true, 'mensaje' => 'Producto ocultado']);
    }

    public function toggleActivo()
    {
        Auth::requireLogin([Auth::ROL_ADMIN]);
        Auth::requireToken();
        Security::requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $activo = (int)($_POST['activo'] ?? 0) === 1;
        $this->model->setActivo($id, $activo);
        echo json_encode(['ok' => true]);
    }

    // Edita el stock por talla (vendedor solo sobre sus propios productos)
    public function actualizarStock()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $stocks = $_POST['stock'] ?? [];

        if ($id <= 0 || !is_array($stocks) || empty($stocks)) {
            echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
            exit;
        }

        if (Auth::rol() === Auth::ROL_VENDEDOR && !$this->model->perteneceA($id, (int)Auth::id())) {
            echo json_encode(['ok' => false, 'error' => 'No puedes editar el stock de este producto']);
            exit;
        }

        foreach ($stocks as $talla => $stock) {
            $talla = trim($talla);
            if ($talla === '') {
                continue;
            }
            $this->model->actualizarStock($id, $talla, max(0, (int)$stock));
        }

        echo json_encode(['ok' => true, 'mensaje' => 'Stock actualizado']);
    }

    private function uploadImage()
    {
        $name = time() . '_' . $_FILES['imagen']['name'];
        move_uploaded_file(
            $_FILES['imagen']['tmp_name'],
            BASE_PATH . '/public/uploads/' . $name
        );
        return '/uploads/' . $name;
    }
}
