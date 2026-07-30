<?php
$count = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BodyShop</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="/css/app.css?v=<?php echo time(); ?>">

    <style>
        /* Estilos rápidos para que el header no se rompa con Bootstrap */
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
    </style>
</head>
<body>

<header>
    <h2><a href="/">BodyShop</a></h2>

    <nav>
        <a href="/?c=Producto&m=index">Tienda</a>

        <div class="cart-wrapper">
            <a href="/?c=Carrito&m=index" class="cart-link">
                🛒 <span id="cart-count"><?= $count ?></span>
            </a>
            <div id="mini-cart" class="mini-cart"></div>
        </div>
        <a href="/?c=AdminAuth&m=index" class="admin-link text-dark">
            <i class="fas fa-cog"></i> Admin
        </a>
    </nav>
</header>