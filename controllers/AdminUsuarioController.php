<?php

require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Security.php';
require_once BASE_PATH . '/repositories/UsuarioRepository.php';

class AdminUsuarioController
{
    private UsuarioRepository $repo;

    public function __construct()
    {
        Auth::requireLogin([Auth::ROL_ADMIN]);

        $this->repo = new UsuarioRepository();
    }

    public function index()
    {
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $data = $this->repo->allPag($pagina, 10);
        $usuarios = $data['usuarios'];
        $paginas = $data['paginas'];
        $pagina = $data['pagina'];
        $total = $data['total'];

        require BASE_PATH . '/views/admin/usuarios/index.php';
    }

    public function storeVendedor()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $nombre = trim($_POST['nombre'] ?? '');
        $email  = strtolower(trim($_POST['email'] ?? ''));
        $clave  = $_POST['clave'] ?? '';
        $marca  = trim($_POST['marca'] ?? '');

        if ($nombre === '' || strlen($nombre) < 3) {
            header('Location: /?c=AdminUsuario&m=index&error=Nombre inválido.');
            exit;
        }
        if (mb_strlen($marca) > 120) {
            header('Location: /?c=AdminUsuario&m=index&error=La marca no puede superar 120 caracteres.');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $this->repo->emailExiste($email)) {
            header('Location: /?c=AdminUsuario&m=index&error=Correo inválido o ya registrado.');
            exit;
        }
        if (!Security::validarPoliticaContrasena($clave)) {
            header('Location: /?c=AdminUsuario&m=index&error=La contraseña debe tener mínimo 8 caracteres, mayúscula, minúscula y número.');
            exit;
        }

        $this->repo->create([
            'nombre' => $nombre,
            'marca' => $marca,
            'email' => $email,
            'password_hash' => Security::hashPassword($clave),
            'password_plano' => $clave,
            'rol' => Auth::ROL_VENDEDOR,
        ]);

        header('Location: /?c=AdminUsuario&m=index&ok=Vendedor creado correctamente.');
        exit;
    }

    public function cambiarActivo()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $activo = ($_POST['activo'] ?? '1') === '1';

        if ($id === Auth::id()) {
            echo json_encode(['ok' => false, 'error' => 'No puedes desactivar tu propia cuenta']);
            exit;
        }

        $this->repo->setActivo($id, $activo);
        echo json_encode(['ok' => true]);
    }

    public function cambiarRol()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $rol = $_POST['rol'] ?? '';

        if ($id === Auth::id()) {
            echo json_encode(['ok' => false, 'error' => 'No puedes cambiar tu propio rol']);
            exit;
        }

        if (!in_array($rol, [Auth::ROL_USUARIO, Auth::ROL_VENDEDOR, Auth::ROL_ADMIN], true)) {
            echo json_encode(['ok' => false, 'error' => 'Rol inválido']);
            exit;
        }

        $this->repo->setRol($id, $rol);
        echo json_encode(['ok' => true]);
    }

    public function resetPassword()
    {
        Auth::requireToken();
        Security::requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $clave = $_POST['clave'] ?? '';

        if (!Security::validarPoliticaContrasena($clave)) {
            echo json_encode(['ok' => false, 'error' => 'La contraseña debe tener mínimo 8 caracteres, mayúscula, minúscula y número.']);
            exit;
        }

        $this->repo->updatePassword($id, Security::hashPassword($clave), $clave);
        echo json_encode(['ok' => true]);
    }
}
