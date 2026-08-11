<?php

require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Security.php';
require_once BASE_PATH . '/core/TokenService.php';
require_once BASE_PATH . '/core/LoginLimiter.php';
require_once BASE_PATH . '/core/Mailer.php';
require_once BASE_PATH . '/repositories/UsuarioRepository.php';
require_once BASE_PATH . '/repositories/PedidoRepository.php';
require_once BASE_PATH . '/repositories/CarritoRepository.php';
require_once BASE_PATH . '/repositories/ResetTokenRepository.php';

class AuthController
{
    private UsuarioRepository $repo;

    public function __construct()
    {
        $this->repo = new UsuarioRepository();
    }

    // ===== Vistas =====
    public function login()
    {
        if (Auth::check()) {
            Auth::redirigirPorRol(Auth::rol());
        }

        $error = $_GET['error'] ?? null;
        $linkRecuperacion = $_SESSION['flash_reset_link'] ?? null;
        unset($_SESSION['flash_reset_link']);
        require BASE_PATH . '/views/auth/login.php';
    }

    public function register()
    {
        if (Auth::check()) {
            Auth::redirigirPorRol(Auth::rol());
        }

        $error = null;
        require BASE_PATH . '/views/auth/register.php';
    }

    public function forgot()
    {
        if (Auth::check()) {
            Auth::redirigirPorRol(Auth::rol());
        }
        require BASE_PATH . '/views/auth/forgot.php';
    }

    public function reset()
    {
        if (Auth::check()) {
            Auth::redirigirPorRol(Auth::rol());
        }

        $token = $_GET['token'] ?? '';
        $valido = (new ResetTokenRepository())->validar(Security::hashToken($token));

        if (!$valido) {
            require BASE_PATH . '/views/auth/reset_invalido.php';
            return;
        }

        require BASE_PATH . '/views/auth/reset.php';
    }

    public function perfil()
    {
        Auth::requireLogin([Auth::ROL_USUARIO]);

        $usuario = $this->repo->find(Auth::id());
        $repoPedidos = new PedidoRepository();
        $pedidos = $repoPedidos->porUsuario(Auth::id());

        require BASE_PATH . '/views/auth/perfil.php';
    }

    // ===== Acciones POST =====
    public function procesarLogin()
    {
        Security::requireCsrf();

        $email = strtolower(trim($_POST['email'] ?? ''));
        $clave = $_POST['clave'] ?? '';

        if (LoginLimiter::estaBloqueado($email)) {
            $seg = LoginLimiter::restanteSegundos($email);
            header('Location: /?c=Auth&m=login&error=' . urlencode('Cuenta bloqueada temporalmente por intentos fallidos. Intenta en ' . ceil($seg / 60) . ' min.'));
            exit;
        }

        $usuario = $this->repo->findByEmail($email);

        if (!$usuario || !Security::verifyPassword($clave, $usuario['password_hash'])) {
            LoginLimiter::registrarFallo($email);
            header('Location: /?c=Auth&m=login&error=' . urlencode('Credenciales incorrectas.'));
            exit;
        }

        if ((int)$usuario['activo'] !== 1) {
            header('Location: /?c=Auth&m=login&error=' . urlencode('Tu cuenta está desactivada. Contacta al administrador.'));
            exit;
        }

        LoginLimiter::limpiar($email);
        Auth::login($usuario);

        // Restaurar el carrito guardado en la BD (si existe)
        if ($usuario['rol'] === Auth::ROL_USUARIO) {
            $repoCarrito = new CarritoRepository();
            $guardado = $repoCarrito->cargar((int)$usuario['id']);
            $_SESSION['cart'] = array_merge($guardado, $_SESSION['cart'] ?? []);
            $repoCarrito->guardar((int)$usuario['id'], $_SESSION['cart']);
        }

        Auth::redirigirPorRol($usuario['rol']);
    }

    public function procesarRegistro()
    {
        Security::requireCsrf();

        $nombre  = trim($_POST['nombre'] ?? '');
        $cedula  = trim($_POST['cedula'] ?? '');
        $email   = strtolower(trim($_POST['email'] ?? ''));
        $clave   = $_POST['clave'] ?? '';
        $confirm = $_POST['clave_confirm'] ?? '';

        $error = $this->validarRegistro($nombre, $cedula, $email, $clave, $confirm);
        if ($error !== null) {
            require BASE_PATH . '/views/auth/register.php';
            exit;
        }

        // Solo se permite el registro de usuarios finales
        $id = $this->repo->create([
            'nombre' => $nombre,
            'cedula' => $cedula,
            'email' => $email,
            'password_hash' => Security::hashPassword($clave),
            'rol' => Auth::ROL_USUARIO,
        ]);

        $usuario = $this->repo->find($id);
        Auth::login($usuario);

        // Guardar el carrito de la sesión (productos elegidos antes de registrarse)
        if ($usuario['rol'] === Auth::ROL_USUARIO) {
            (new CarritoRepository())->guardar((int)$usuario['id'], $_SESSION['cart'] ?? []);
        }

        Auth::redirigirPorRol(Auth::ROL_USUARIO);
    }

    public function procesarForgot()
    {
        Security::requireCsrf();

        $email = strtolower(trim($_POST['email'] ?? ''));

        // Respuesta genérica para no revelar qué correos existen
        $mensaje = 'Si el correo existe en nuestro sistema, recibirás un enlace para restablecer tu contraseña.';

        $usuario = $this->repo->findByEmail($email);
        if ($usuario && (int)$usuario['activo'] === 1) {
            $repoTokens = new ResetTokenRepository();
            $token = Security::generarToken();
            $repoTokens->crear($usuario['id'], Security::hashToken($token));
            $repoTokens->limpiarExpirados();

            $enlace = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost:8080') . "/?c=Auth&m=reset&token=$token";
            $mailOk = Mailer::send(
                $usuario['email'],
                'Restablecer tu contraseña - BodyStop',
                "<p>Hola {$usuario['nombre']},</p>
                 <p>Recibimos una solicitud para restablecer tu contraseña.</p>
                 <p><a href=\"$enlace\">Haz clic aquí para restablecerla</a></p>
                 <p>El enlace expira en 30 minutos. Si no lo solicitaste, ignora este correo.</p>"
            );

            if (!getenv('MAIL_HOST')) {
                $_SESSION['flash_reset_link'] = $enlace;
            }
        }

        header('Location: /?c=Auth&m=login&ok=' . urlencode($mensaje));
        exit;
    }

    public function procesarReset()
    {
        Security::requireCsrf();

        $token = $_POST['token'] ?? '';
        $clave = $_POST['clave'] ?? '';
        $confirm = $_POST['clave_confirm'] ?? '';

        $repoTokens = new ResetTokenRepository();
        $registro = $repoTokens->validar(Security::hashToken($token));

        if (!$registro) {
            header('Location: /?c=Auth&m=login&error=' . urlencode('El enlace es inválido o ha expirado. Solicita uno nuevo.'));
            exit;
        }

        if ($clave !== $confirm || !Security::validarPoliticaContrasena($clave)) {
            require BASE_PATH . '/views/auth/reset.php';
            exit;
        }

        $this->repo->updatePassword((int)$registro['usuario_id'], Security::hashPassword($clave), $clave);
        $repoTokens->marcarUsado((int)$registro['id']);

        header('Location: /?c=Auth&m=login&ok=' . urlencode('Contraseña restablecida correctamente. Ya puedes iniciar sesión.'));
        exit;
    }

    // Cambiar contraseña estando logueado (pide la contraseña actual)
    public function cambiarPassword()
    {
        Auth::requireLogin([Auth::ROL_USUARIO]);
        Auth::requireToken();
        Security::requireCsrf();

        $actual = $_POST['clave_actual'] ?? '';
        $nueva = $_POST['clave_nueva'] ?? '';
        $confirm = $_POST['clave_confirm'] ?? '';

        $usuario = $this->repo->find(Auth::id());

        if (!$usuario || !Security::verifyPassword($actual, $usuario['password_hash'])) {
            echo json_encode(['ok' => false, 'error' => 'La contraseña actual es incorrecta']);
            exit;
        }
        if ($nueva !== $confirm) {
            echo json_encode(['ok' => false, 'error' => 'Las contraseñas nuevas no coinciden']);
            exit;
        }
        if (!Security::validarPoliticaContrasena($nueva)) {
            echo json_encode(['ok' => false, 'error' => 'La contraseña debe tener mínimo 8 caracteres, mayúscula, minúscula y número']);
            exit;
        }

        $this->repo->updatePassword(Auth::id(), Security::hashPassword($nueva), $nueva);

        if (Auth::apiToken()) {
            TokenService::revocar(Auth::apiToken());
            $_SESSION['api_token'] = TokenService::emitir(Auth::id(), 'api', 1)['token'];
        }

        echo json_encode(['ok' => true, 'mensaje' => 'Contraseña actualizada']);
    }

    // Editar nombre/correo del perfil
    public function actualizarPerfil()
    {
        Auth::requireLogin([Auth::ROL_USUARIO]);
        Auth::requireToken();
        Security::requireCsrf();

        $nombre = trim($_POST['nombre'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));

        if (strlen($nombre) < 3) {
            echo json_encode(['ok' => false, 'error' => 'El nombre debe tener al menos 3 caracteres']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok' => false, 'error' => 'Correo inválido']);
            exit;
        }
        if ($this->repo->emailExiste($email, Auth::id())) {
            echo json_encode(['ok' => false, 'error' => 'Ese correo ya está en uso por otra cuenta']);
            exit;
        }

        $this->repo->update(Auth::id(), ['nombre' => $nombre, 'email' => $email]);
        $_SESSION['user_nombre'] = $nombre;

        echo json_encode(['ok' => true, 'mensaje' => 'Datos actualizados']);
    }

    public function logout()
    {
        Auth::logout();
        header('Location: /');
        exit;
    }

    // ===== Validación =====
    private function validarRegistro(string $nombre, string $cedula, string $email, string $clave, string $confirm): ?string
    {
        if ($nombre === '' || strlen($nombre) < 3) {
            return 'El nombre debe tener al menos 3 caracteres.';
        }
        if ($cedula === '' || !ctype_digit($cedula) || strlen($cedula) < 7 || strlen($cedula) > 10) {
            return 'Ingresa un número de cédula válido (7 a 10 dígitos).';
        }
        if ($this->repo->cedulaExiste($cedula)) {
            return 'Ya existe una cuenta con esa cédula.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Ingresa un correo electrónico válido.';
        }
        if ($this->repo->emailExiste($email)) {
            return 'Ya existe una cuenta con ese correo.';
        }
        if (!Security::validarPoliticaContrasena($clave)) {
            return 'La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula y un número.';
        }
        if ($clave !== $confirm) {
            return 'Las contraseñas no coinciden.';
        }
        return null;
    }
}
