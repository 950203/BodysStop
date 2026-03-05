<h1>➕ Nuevo producto</h1>

<form method="POST" action="/?c=AdminProducto&m=store" enctype="multipart/form-data">
    <input type="text" name="nombre" placeholder="Nombre" required>
    <input type="number" name="precio" placeholder="Precio" required>
    <input type="file" name="imagen" required>
    <button>Guardar</button>
</form>