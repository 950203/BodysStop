<?php $error = $error ?? null; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta | BodyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="auth-body">

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <i class="fas fa-user-plus"></i>
            <h1>Crear cuenta</h1>
            <p>Regístrate para comprar</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i><?= Security::escape($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/?c=Auth&m=procesarRegistro" autocomplete="off" id="register-form">
            <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

            <div class="mb-3">
                <label class="form-label small text-muted">Nombre completo</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="nombre" class="form-control" placeholder="María Pérez" required minlength="3">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small text-muted">Correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="tucorreo@ejemplo.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small text-muted">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="clave" class="form-control" placeholder="Mín. 8 caracteres" required>
                </div>
                <div class="form-text small">Mínimo 8 caracteres, con mayúscula, minúscula y número.</div>
            </div>

            <div class="mb-4">
                <label class="form-label small text-muted">Confirmar contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="clave_confirm" class="form-control" placeholder="Repite la contraseña" required>
                </div>
            </div>

            <button type="submit" class="btn btn-dark w-100">
                <i class="fas fa-user-plus me-1"></i> Crear cuenta
            </button>
        </form>

        <hr class="my-4">

        <p class="text-center mb-0 small">
            ¿Ya tienes cuenta? <a href="/?c=Auth&m=login"><strong>Inicia sesión</strong></a>
        </p>
    </div>
</div>

</body>
<script src="/js/validacion.js"></script>
</html>
