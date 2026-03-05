<?php
 
class AdminProductoController
{
    private $model;
 
    public function __construct()
    {
        $this->model = new ProductoModel();
    }
 
    public function index()
    {
        $productos = $this->model->all();
        require BASE_PATH . '/views/admin/productos/index.php';
    }
 
    public function create()
    {
        require BASE_PATH . '/views/admin/productos/create.php';
    }
 
    public function store()
    {
        $imagen = $this->uploadImage();
 
        $this->model->create([
            'nombre' => $_POST['nombre'],
            'precio' => $_POST['precio'],
            'imagen' => $imagen
        ]);
 
        header('Location: /?c=AdminProducto&m=index');
    }
 
    public function edit()
    {
        $producto = $this->model->find($_GET['id']);
        require BASE_PATH . '/views/admin/productos/edit.php';
    }
 
    public function update()
    {
        $imagen = $_POST['imagen_actual'];
 
        if (!empty($_FILES['imagen']['name'])) {
            $imagen = $this->uploadImage();
        }
 
        $this->model->update($_POST['id'], [
            'nombre' => $_POST['nombre'],
            'precio' => $_POST['precio'],
            'imagen' => $imagen
        ]);
 
        header('Location: /?c=AdminProducto&m=index');
    }
 
    public function delete()
    {
        $this->model->delete($_POST['id']);
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