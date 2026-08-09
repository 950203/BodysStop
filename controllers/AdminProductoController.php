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
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $data = $this->model->allPag($pagina, 10);
        $productos = $data['productos'];
        $paginas = $data['paginas'];
        $pagina = $data['pagina'];
        require BASE_PATH . '/views/admin/productos/index.php';
    }

    public function create()
    {
        $categorias = $this->model->categorias();
        require BASE_PATH . '/views/admin/productos/create.php';
    }

    public function store()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $imagen = $this->uploadImage();

        $id = $this->model->create([
            'nombre' => $_POST['nombre'],
            'descripcion' => $_POST['descripcion'] ?? '',
            'precio' => $_POST['precio'],
            'imagen' => $imagen,
            'categoria_id' => $_POST['categoria_id'] ?? '',
        ]);

        $this->model->guardarTallas((int)$id, $this->tallasDesdePost());

        header('Location: /?c=AdminProducto&m=index');
    }

    public function edit()
    {
        $producto = $this->model->find($_GET['id']);
        $categorias = $this->model->categorias();
        $tallas = $this->model->tallas((int)$producto['id']);
        require BASE_PATH . '/views/admin/productos/edit.php';
    }

    public function update()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $imagen = $_POST['imagen_actual'];

        if (!empty($_FILES['imagen']['name'])) {
            $imagen = $this->uploadImage();
        }

        $this->model->update($_POST['id'], [
            'nombre' => $_POST['nombre'],
            'descripcion' => $_POST['descripcion'] ?? '',
            'precio' => $_POST['precio'],
            'imagen' => $imagen,
            'categoria_id' => $_POST['categoria_id'] ?? '',
        ]);

        $this->model->guardarTallas((int)$_POST['id'], $this->tallasDesdePost());

        header('Location: /?c=AdminProducto&m=index');
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
        Auth::requireToken();
        Security::requireCsrf();

        // Soft delete: oculta el producto de la tienda
        $this->model->delete($_POST['id']);
        echo json_encode(['ok' => true, 'mensaje' => 'Producto ocultado']);
    }

    public function toggleActivo()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $activo = (int)($_POST['activo'] ?? 0) === 1;
        $this->model->setActivo($id, $activo);
        echo json_encode(['ok' => true]);
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
