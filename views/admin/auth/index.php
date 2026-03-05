<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="/css/app.css">
    <?php require BASE_PATH . '/views/layouts/header.php'; ?>
    <title>Login Administrador</title>
    <style>
        body {
            font-family: Arial;
            background: #f2f2f2;
        }

        .login-box {
            width: 300px;
            margin: 300px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 10px gray;
            text-align: center;
        }

        input {
            width: 200px;
            display: block;
            margin: 30px 0;
        }

        button {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .container {
            display: flex;
            justify-content: center;
            margin-top: 50px;
        }

        .btn-success {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            align-items: center;
        }
    </style>
</head>

<body>

    <div class="login">
        <div class="container">
            <div class="row">
                <div class="card w-100">
                    <div class="card-body">
                        <h2>Login Administrador</h2>
                    </div>
                    <form method="POST" action="/?c=AdminAuth&m=login">
                        <input type="text" class="form-control" name="correo" placeholder="Correo" required>
                        <input type="password" class="form-control" name="clave" placeholder="Contraseña" required>
                        <div class="col text-center">
                            <button type="button" class="btn btn-success">Ingresar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>