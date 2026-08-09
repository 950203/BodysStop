<?php

class Auth
{
    public const ROL_USUARIO = 'usuario';
    public const ROL_VENDEDOR = 'vendedor';
    public const ROL_ADMIN = 'administrador';

    // Inicia sesión: regenera la sesión y emite un token API
    public static function login(array $usuario): void
    {
        Security::regenerarSesion();
        $_SESSION['user_id']     = (int)$usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombre'];
        $_SESSION['user_rol']    = $usuario['rol'];
        $_SESSION['api_token']   = TokenService::emitir($usuario['id'], 'api', 1)['token'];
    }

    public static function logout(): void
    {
        if (!empty($_SESSION['api_token'])) {
            TokenService::revocar($_SESSION['api_token']);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function rol(): ?string
    {
        return $_SESSION['user_rol'] ?? null;
    }

    public static function nombre(): ?string
    {
        return $_SESSION['user_nombre'] ?? null;
    }

    public static function tieneRol(...$roles): bool
    {
        return in_array(self::rol(), $roles, true);
    }

    public static function apiToken(): ?string
    {
        return $_SESSION['api_token'] ?? null;
    }

    // Redirige según el rol luego del login
    public static function redirigirPorRol(?string $rol): void
    {
        switch ($rol) {
            case self::ROL_ADMIN:
                header('Location: /?c=AdminUsuario&m=index');
                break;
            case self::ROL_VENDEDOR:
                header('Location: /?c=AdminProducto&m=index');
                break;
            default:
                header('Location: /?c=Carrito&m=index');
        }
        exit;
    }

    // Guard para páginas: exige sesión (opcionalmente un rol específico)
    public static function requireLogin(array $roles = null): void
    {
        if (!self::check()) {
            header('Location: /?c=Auth&m=login');
            exit;
        }
        if ($roles !== null && !self::tieneRol(...$roles)) {
            http_response_code(403);
            die("Acceso denegado: no tienes permisos para esta sección.");
        }
    }

    // Guard para endpoints JSON: exige sesión + rol
    public static function requireLoginJson(array $roles = null): void
    {
        if (!self::check()) {
            http_response_code(401);
            die(json_encode(['ok' => false, 'error' => 'No autenticado']));
        }
        if ($roles !== null && !self::tieneRol(...$roles)) {
            http_response_code(403);
            die(json_encode(['ok' => false, 'error' => 'Sin permisos']));
        }
    }

    // Guard para endpoints CRUD: valida el token API (header X-Auth-Token o campo api_token)
    public static function requireToken(): void
    {
        self::requireLoginJson();

        $tokenEntrada = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? ($_POST['api_token'] ?? '');
        $token = TokenService::validar($tokenEntrada);

        if (!$token || (int)$token['usuario_id'] !== self::id()) {
            http_response_code(401);
            die(json_encode(['ok' => false, 'error' => 'Token inválido o expirado']));
        }
    }
}
