<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-light mb-0" style="letter-spacing:1px;">Gestión de Usuarios</h1>
        <span class="badge bg-dark"><i class="fas fa-shield-alt me-1"></i> Administrador</span>
    </div>

    <?php if (isset($_GET['ok'])): ?>
        <div class="alert alert-success py-2 small"><i class="fas fa-check-circle me-1"></i><?= Security::escape($_GET['ok']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger py-2 small"><i class="fas fa-exclamation-circle me-1"></i><?= Security::escape($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <strong><i class="fas fa-user-tie me-2"></i> Crear Vendedor</strong>
                </div>
                <div class="card-body">
                    <form method="POST" action="/?c=AdminUsuario&m=storeVendedor" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

                        <div class="mb-3">
                            <label class="form-label small text-muted">Nombre completo</label>
                            <input type="text" name="nombre" class="form-control" required minlength="3">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Marca / Nombre del negocio</label>
                            <input type="text" name="marca" class="form-control" placeholder="Ej: Cuerpo Fino">
                            <div class="form-text small">Esta marca se mostrará en sus productos.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Correo electrónico</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Contraseña inicial</label>
                            <input type="password" name="clave" class="form-control" required>
                            <div class="form-text small">Mín. 8 caracteres, mayúscula, minúscula y número.</div>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">
                            <i class="fas fa-plus me-1"></i> Crear vendedor
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <strong><i class="fas fa-users me-2"></i> Usuarios registrados</strong>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Marca</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td class="fw-semibold"><?= Security::escape($u['nombre']) ?></td>
                                    <td><?= !empty($u['marca']) ? Security::escape($u['marca']) : '<span class="text-muted">—</span>' ?></td>
                                    <td><?= Security::escape($u['email']) ?></td>
                                    <td>
                                        <select class="form-select form-select-sm rol-select" data-id="<?= $u['id'] ?>" <?= $u['id'] === Auth::id() ? 'disabled' : '' ?>>
                                            <?php foreach (['usuario', 'vendedor', 'administrador'] as $r): ?>
                                                <option value="<?= $r ?>" <?= $u['rol'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <?php if ((int)$u['activo'] === 1): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary activo-btn" data-id="<?= $u['id'] ?>" data-activo="<?= (int)$u['activo'] ?>" <?= $u['id'] === Auth::id() ? 'disabled' : '' ?> title="Cambiar estado">
                                            <i class="fas <?= (int)$u['activo'] === 1 ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning pass-btn" data-id="<?= $u['id'] ?>" data-nombre="<?= Security::escape($u['nombre']) ?>" <?= $u['id'] === Auth::id() ? 'disabled' : '' ?> title="Restablecer contraseña">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($paginas > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination justify-content-center mb-0">
                                <?php for ($i = 1; $i <= $paginas; $i++): ?>
                                    <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                        <a class="page-link" href="/?c=AdminUsuario&m=index&pagina=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/js/admin-usuarios.js"></script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
