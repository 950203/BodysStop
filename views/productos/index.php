<!DOCTYPE html>
<html>

<head>
    <title>BodyShop</title>
    <!-- <link rel="stylesheet" href="/css/app.css"> -->
    <?php require BASE_PATH . '/views/layouts/header.php'; ?>
    <!-- <style>
        .grid {
            display: flex;
            gap: 20px;
        }

        .card {
            border: 1px solid #ddd;
            padding: 10px;
            width: 200px;
        }

        button {
            background: black;
            color: white;
            padding: 5px;
            cursor: pointer;
        }
    </style> -->
</head>

<body>

    <h1>Bodys para Damas 💃</h1>

    <div class="grid">
        <?php foreach ($productos as $p): ?>
            <div class="card">
                <img src="<?= $p['imagen'] ?>" width="100%">
                <h3><?= $p['nombre'] ?></h3>
                <p>$<?= number_format($p['precio']) ?></p>
                <button onclick="addToCart(<?= $p['id'] ?>)">Agregar</button>
            </div>
        <?php endforeach ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/cart.js"></script>
</body>

</html>