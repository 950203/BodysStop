<?php
$error = $error ?? null;
$ok = $_GET['ok'] ?? null;
$linkRecuperacion = $linkRecuperacion ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | BodyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="auth-body">

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <i class="fas fa-store"></i>
            <h1>BodyShop</h1>
            <p>Bienvenido de nuevo</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i><?= Security::escape($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($ok): ?>
            <div class="alert alert-success py-2 small" role="alert">
                <i class="fas fa-check-circle me-1"></i><?= Security::escape($ok) ?>
            </div>
        <?php endif; ?>

        <?php if ($linkRecuperacion): ?>
            <div class="alert alert-info py-2 small" role="alert">
                <i class="fas fa-envelope me-1"></i>
                <strong>Modo desarrollo:</strong> como no hay servidor de correo configurado, aquí está tu enlace de restablecimiento:<br>
                <a href="<?= Security::escape($linkRecuperacion) ?>"><?= Security::escape($linkRecuperacion) ?></a>
            </div>
        <?php endif; ?>

        <form method="POST" action="/?c=Auth&m=procesarLogin" autocomplete="off" id="login-form">
            <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

            <div class="mb-3">
                <label class="form-label small text-muted">Correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="tucorreo@ejemplo.com" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small text-muted">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="clave" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-dark w-100">
                <i class="fas fa-sign-in-alt me-1"></i> Ingresar
            </button>
        </form>

        <div class="text-end mt-2">
            <a href="/?c=Auth&m=forgot" class="small text-muted">¿Olvidaste tu contraseña?</a>
        </div>

        <hr class="my-4">

        <p class="text-center mb-0 small">
            ¿No tienes cuenta? <a href="/?c=Auth&m=register"><strong>Regístrate</strong></a>
        </p>
    </div>
</div>

</body>
<script src="/js/validacion.js"></script>
</html>
