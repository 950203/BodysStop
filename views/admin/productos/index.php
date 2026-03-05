<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="/css/app.css">

<div class="container">
    <div class="main-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 fw-bold"><i class="fas fa-boxes me-2"></i> Gestión de Productos</h2>
            <a href="/?c=AdminProducto&m=create" class="btn btn-dark shadow-sm">
                <i class="fas fa-plus me-1"></i> Nuevo Producto
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="80">Miniatura</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                        <tr id="row-<?= $p['id'] ?>">
                            <td>
                                <img src="<?= $p['imagen'] ?>" class="product-img border" alt="producto">
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($p['nombre']) ?></div>
                                <small class="text-muted">SKU: <?= str_pad($p['id'], 5, "0", STR_PAD_LEFT) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">
                                    $<?= number_format($p['precio'], 2) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="/?c=AdminProducto&m=edit&id=<?= $p['id'] ?>"
                                    class="btn btn-outline-primary btn-action me-1" title="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <button onclick="deleteProduct(<?= $p['id'] ?>)"
                                    class="btn btn-outline-danger btn-action" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($productos)): ?>
            <div class="alert alert-light text-center border-dashed py-5">
                <i class="fas fa-search fa-2x text-muted mb-2"></i>
                <p class="mb-0">No se encontraron productos en la base de datos.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteProduct(id) {
        Swal.fire({
            title: '¿Eliminar producto?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, borrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/?c=AdminProducto&m=delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'id=' + id
                    })
                    .then(response => {
                        const row = document.getElementById('row-' + id);
                        row.classList.add('row-fade-out');
                        setTimeout(() => row.remove(), 400);

                        Swal.fire({
                            title: 'Eliminado',
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    });
            }
        });
    }
</script>