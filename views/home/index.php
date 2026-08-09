<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>BodyStop | Bodys para Damas</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: #fafafa;
            color: #333;
        }

        header {
            background: #000;
            color: #fff;
            padding: 20px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 24px;
            letter-spacing: 1px;
        }

        header a {
            color: #fff;
            text-decoration: none;
            border: 1px solid #fff;
            padding: 8px 16px;
            border-radius: 20px;
            transition: 0.3s;
        }

        header a:hover {
            background: #fff;
            color: #000;
        }

        .hero {
            height: calc(100vh - 80px);
            background: linear-gradient(rgba(0,0,0,.4), rgba(0,0,0,.4)),
                        url('https://images.unsplash.com/photo-1520975916090-3105956dac38') center/cover;
            display: flex;
            align-items: center;
            padding: 60px;
            color: white;
        }

        .hero-content {
            max-width: 500px;
        }

        .hero-content h2 {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .hero-content p {
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .hero-content a {
            display: inline-block;
            background: #fff;
            color: #000;
            padding: 14px 30px;
            border-radius: 30px;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s;
        }

        .hero-content a:hover {
            background: #000;
            color: #fff;
            border: 1px solid #fff;
        }

        footer {
            text-align: center;
            padding: 20px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>

<header>
    <h1>BodyStop</h1>
    <a href="/?c=Producto&m=index">Ver tienda</a>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Resalta tu estilo</h2>
        <p>
            Descubre nuestra colección exclusiva de bodys para damas,
            diseñados para realzar tu figura y acompañarte en cada ocasión.
        </p>
        <a href="/?c=Producto&m=index">Comprar ahora</a>
    </div>
</section>

<footer>
    © <?= date('Y') ?> BodyStop · Moda femenina
</footer>

</body>
</html>
