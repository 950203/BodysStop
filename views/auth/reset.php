<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña | BodyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="auth-body">

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <i class="fas fa-unlock-alt"></i>
            <h1>Nueva contraseña</h1>
            <p>Ingresa tu nueva contraseña</p>
        </div>

        <form method="POST" action="/?c=Auth&m=procesarReset" autocomplete="off" id="reset-form">
            <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">
            <input type="hidden" name="token" value="<?= Security::escape($token) ?>">

            <div class="mb-3">
                <label class="form-label small text-muted">Nueva contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="clave" class="form-control" placeholder="Mín. 8 caracteres, mayúscula, minúscula y número" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small text-muted">Confirmar contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="clave_confirm" class="form-control" placeholder="Repite la contraseña" required>
                </div>
            </div>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <i class="fas fa-exclamation-circle me-1"></i><?= Security::escape($error) ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-dark w-100">
                <i class="fas fa-check me-1"></i> Guardar contraseña
            </button>
        </form>

        <hr class="my-4">

        <p class="text-center mb-0 small">
            <a href="/?c=Auth&m=login"><i class="fas fa-arrow-left me-1"></i>Volver al inicio de sesión</a>
        </p>
    </div>
</div>

</body>
<script src="/js/validacion.js"></script>
</html>
