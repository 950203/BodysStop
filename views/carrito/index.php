<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Carrito | BodyShop</title>
    <link rel="stylesheet" href="/css/app.css">
    <?php require BASE_PATH . '/views/layouts/header.php'; ?>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            /* padding: 40px; */
        }

        .cart-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }

        table {
            width: 100%;
            background: #fff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        th,
        td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #000;
            color: #fff;
        }

        .total {
            text-align: right;
            font-size: 20px;
            margin-top: 20px;
        }

        button {
            padding: 8px 14px;
            cursor: pointer;
            border: none;
        }

        .danger {
            background: #c0392b;
            color: white;
        }

        .success {
            background: #000;
            color: white;
        }

        a {
            text-decoration: none;
            color: white;
        }
    </style>
</head>

<body>

    <h1>🛒 Tu carrito</h1>

    <?php if (empty($items)): ?>
        <p>Tu carrito está vacío.</p>
    <?php else: ?>
        <div class="cart-container">
            <table>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>

                <?php foreach ($items as $item): ?>
                    <tr id="row-<?= $item['id'] ?>">
                        <td><?= $item['nombre'] ?></td>
                        <td>$<?= number_format($item['precio']) ?></td>
                        <td>
                            <button onclick="restar(<?= $item['id'] ?>)">−</button>
                            <?= $item['qty'] ?>
                            <button onclick="sumar(<?= $item['id'] ?>)">+</button>
                        </td>
                        <td class="subtotal">$<?= number_format($item['subtotal']) ?></td>
                        <td>
                            <button class="danger" onclick="removeItem(<?= $item['id'] ?>)">X</button>
                        </td>
                    </tr>
                <?php endforeach ?>

            </table>

            <div class="total">
                <strong>Total: $<?= number_format($total) ?></strong>
            </div>

            <br>

            <button class="success">
                <a href="/?c=Checkout&m=index">Ir al checkout</a>
            </button>

        <?php endif ?>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function removeItem(id) {
                fetch('/?c=Carrito&m=remove', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'id=' + id
                    })
                    .then(res => res.json())
                    .then(() => {
                        const row = document.getElementById('row-' + id);
                        row.classList.add('removing');

                        setTimeout(() => {
                            row.remove();
                            updateCartCount();
                            recalcularTotal();
                        }, 300);
                    });
            }


            function sumar(id) {
                fetch('/?c=Carrito&m=sumar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + id
                }).then(() => location.reload());
            }

            function restar(id) {
                fetch('/?c=Carrito&m=restar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + id
                }).then(() => location.reload());
            }

            function recalcularTotal() {
                let total = 0;

                document.querySelectorAll('.subtotal').forEach(td => {
                    total += parseInt(td.textContent.replace(/\D/g, ''));
                });

                document.querySelector('.total strong').textContent =
                    'Total: $' + total.toLocaleString();
            }
        </script>

</body>

</html>