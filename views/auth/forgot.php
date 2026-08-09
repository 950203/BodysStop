<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña | BodyShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="auth-body">

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <i class="fas fa-key"></i>
            <h1>Recuperar contraseña</h1>
            <p>Te enviaremos un enlace a tu correo</p>
        </div>

        <form method="POST" action="/?c=Auth&m=procesarForgot" autocomplete="off" id="forgot-form">
            <input type="hidden" name="csrf_token" value="<?= Security::csrfToken() ?>">

            <div class="mb-3">
                <label class="form-label small text-muted">Correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="tucorreo@ejemplo.com" required autofocus>
                </div>
            </div>

            <button type="submit" class="btn btn-dark w-100">
                <i class="fas fa-paper-plane me-1"></i> Enviar enlace
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
