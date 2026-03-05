<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Checkout | BodyShop</title>

    <style>
        body {
            font-family: Arial;
            background: #fafafa;
            padding: 40px;
        }

        form {
            background: white;
            padding: 30px;
            max-width: 500px;
            margin: auto;
        }

        input,
        button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
        }

        button {
            background: black;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <h1>💳 Checkout</h1>

    <form id="checkoutForm">
        <input type="text" name="nombre" placeholder="Nombre completo" required>
        <input type="email" name="email" placeholder="Correo" required>
        <input type="text" name="direccion" placeholder="Dirección de envío" required>

        <button type="submit">Confirmar compra</button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('checkoutForm').addEventListener('submit', e => {
            e.preventDefault();

            fetch('/?c=Checkout&m=process', {
                    method: 'POST'
                })
                .then(r => r.json())
                .then(() => {
                    Swal.fire(
                        'Compra exitosa 🎉',
                        'Gracias por tu compra',
                        'success'
                    ).then(() => {
                        window.location.href = '/';
                    });
                });
        });
    </script>

</body>

</html>