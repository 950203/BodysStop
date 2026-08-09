<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace inválido | BodyStop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="auth-body">

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-brand">
            <i class="fas fa-exclamation-triangle"></i>
            <h1>Enlace inválido</h1>
        </div>

        <p class="text-center text-muted small">
            Este enlace de restablecimiento es inválido, ya fue usado o expiró (30 minutos).
        </p>

        <a href="/?c=Auth&m=forgot" class="btn btn-dark w-100 mb-2">
            <i class="fas fa-redo me-1"></i> Solicitar un nuevo enlace
        </a>
        <a href="/?c=Auth&m=login" class="btn btn-outline-secondary w-100">
            <i class="fas fa-arrow-left me-1"></i> Ir al inicio de sesión
        </a>
    </div>
</div>

</body>
</html>
