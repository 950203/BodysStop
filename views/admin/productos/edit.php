<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-light mb-0" style="letter-spacing:1px;">Editar Producto</h1>
        <a href="/?c=AdminProducto&m=index" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Volver</a>
    </div>

    <form method="POST" action="/?c=AdminProducto&m=update" enctype="multipart/form-data" class="row g-4" data-validar-producto>
        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
        <input type="hidden" name="api_token" value="<?= Auth::apiToken() ?>">
        <input type="hidden" name="id" value="<?= $producto['id'] ?>">
        <input type="hidden" name="imagen_actual" value="<?= $producto['imagen'] ?>">

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Nombre del producto</label>
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($producto['nombre']) ?>" required minlength="3">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Precio (COP)</label>
                            <input type="number" name="precio" class="form-control" value="<?= $producto['precio'] ?>" required min="1" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Categoría</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">Sin categoría</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (int)$producto['categoria_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Material / Tela</label>
                        <input type="text" name="material" class="form-control" value="<?= htmlspecialchars($producto['material'] ?? '') ?>" placeholder="Ej: Encaje elástico, Algodón suave, Satín...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="4"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Imagen</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*">
                        <small class="text-muted">Déjalo vacío para conservar la imagen actual.</small>
                        <div class="mt-2">
                            <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="" style="width:80px;height:100px;object-fit:cover;border-radius:8px;">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Vendedor</label>
                            <select name="vendedor_id" class="form-select" id="producto-vendedor">
                                <option value="">Sin vendedor</option>
                                <?php foreach ($vendedores as $v): ?>
                                    <option value="<?= $v['id'] ?>" data-marca="<?= htmlspecialchars($v['marca'] ?? '') ?>" <?= (int)($producto['vendedor_id'] ?? 0) === (int)$v['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(($v['marca'] ? $v['marca'] . ' — ' : '') . $v['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Marca (nombre del vendedor)</label>
                            <input type="text" name="marca" id="producto-marca" class="form-control" value="<?= htmlspecialchars($producto['marca'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
                    <?php $soloLectura = Auth::rol() !== Auth::ROL_ADMIN; ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <strong><i class="fas fa-ruler me-2"></i> Stock por talla</strong>
                        </div>
                        <div class="card-body">
                            <?php $stockPorTalla = array_column($tallas, 'stock', 'talla'); ?>
                            <div class="row g-2 mb-2">
                                <div class="col-7"><small class="text-muted">Talla</small></div>
                                <div class="col-5"><small class="text-muted">Stock</small></div>
                            </div>
                            <?php foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $talla): ?>
                                <div class="row g-2 mb-2 align-items-center">
                                    <div class="col-7">
                                        <span class="badge bg-light text-dark border"><?= $talla ?></span>
                                    </div>
                                    <div class="col-5">
                                        <input type="number" name="talla[<?= $talla ?>]" class="form-control form-control-sm" value="<?= $stockPorTalla[$talla] ?? 0 ?>" min="0" <?= $soloLectura ? 'disabled' : '' ?>>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($soloLectura): ?>
                                <small class="text-muted d-block"><i class="fas fa-lock me-1"></i>El stock solo puede ser editado por el administrador.</small>
                            <?php else: ?>
                                <small class="text-muted">Deja en 0 las tallas que no quieras ofrecer.</small>
                            <?php endif; ?>
                        </div>
                    </div>
        </div>

        <div class="col-12">
            <button class="btn btn-dark px-4"><i class="fas fa-save me-1"></i> Actualizar producto</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/js/validacion.js"></script>
<script>
    // Al elegir vendedor, se autocompleta la marca
    document.getElementById('producto-vendedor').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        document.getElementById('producto-marca').value = opt && opt.dataset.marca ? opt.dataset.marca : '';
    });
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
