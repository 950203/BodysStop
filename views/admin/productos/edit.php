<h1>✏️ Editar producto</h1>

<form method="POST" action="/?c=AdminProducto&m=update" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $producto['id'] ?>">
    <input type="hidden" name="imagen_actual" value="<?= $producto['imagen'] ?>">

    <input type="text" name="nombre" value="<?= $producto['nombre'] ?>" required>
    <input type="number" name="precio" value="<?= $producto['precio'] ?>" required>

    <img src="<?= $producto['imagen'] ?>" width="80"><br>
    <input type="file" name="imagen">

    <button>Actualizar</button>
</form>