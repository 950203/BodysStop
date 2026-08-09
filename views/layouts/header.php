<?php
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Security.php';

$logueado = Auth::check();
$rol = Auth::rol();
$nombre = Auth::nombre();
$count = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BodyStop</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="/css/app.css?v=<?php echo time(); ?>">

    <style>
        header {
            background: #fff;
            padding: 1rem 2rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h2 a { text-decoration: none; color: #000; font-weight: bold; }
        nav a { margin-left: 15px; text-decoration: none; color: #555; }
        .cart-wrapper { display: inline-block; position: relative; }
        #cart-count {
            background: #ff4757;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            vertical-align: top;
        }
        .pop { transform: scale(1.2); transition: 0.3s; }
        .nav-user { font-weight: 600; color: #333; }
    </style>
</head>
<body>

<header>
    <h2><a href="/">BodyStop</a></h2>

    <nav>
        <a href="/?c=Producto&m=index">Tienda</a>

        <?php if ($logueado): ?>

            <?php if (in_array($rol, [Auth::ROL_VENDEDOR, Auth::ROL_ADMIN], true)): ?>
                <a href="/?c=AdminProducto&m=index">Productos</a>
                <a href="/?c=AdminPedido&m=index">Pedidos</a>
                <a href="/?c=AdminPedido&m=dashboard">Dashboard</a>
            <?php endif ?>

            <?php if ($rol === Auth::ROL_ADMIN): ?>
                <a href="/?c=AdminUsuario&m=index">Usuarios</a>
            <?php endif ?>

            <?php if ($rol === Auth::ROL_USUARIO): ?>
                <div class="cart-wrapper">
                    <a href="/?c=Carrito&m=index" class="cart-link">
                        🛒 <span id="cart-count"><?= $count ?></span>
                    </a>
                    <div id="mini-cart" class="mini-cart"></div>
                </div>
                <a href="/?c=Auth&m=perfil" class="nav-user"><i class="fas fa-user"></i> <?= Security::escape($nombre) ?></a>
            <?php else: ?>
                <span class="nav-user"><i class="fas fa-shield-alt"></i> <?= Security::escape($nombre) ?> (<?= Security::escape($rol) ?>)</span>
            <?php endif ?>

            <a href="/?c=Auth&m=logout" title="Cerrar sesión"><i class="fas fa-sign-out-alt"></i></a>

        <?php else: ?>
            <a href="/?c=Auth&m=login"><i class="fas fa-sign-in-alt"></i> Ingresar</a>
            <a href="/?c=Auth&m=register">Registrarse</a>
        <?php endif ?>
    </nav>
</header>

<script>
    window.API_TOKEN = <?= $logueado && Auth::apiToken() ? json_encode(Auth::apiToken()) : 'null' ?>;
    window.CSRF_TOKEN = <?= json_encode(Security::csrfToken()) ?>;
</script>
