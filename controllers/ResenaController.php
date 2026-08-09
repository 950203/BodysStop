<?php

require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Security.php';
require_once BASE_PATH . '/repositories/ResenaRepository.php';

class ResenaController
{
    public function store()
    {
        Auth::requireLogin([Auth::ROL_USUARIO]);
        Auth::requireToken();
        Security::requireCsrf();

        $productoId = (int)($_POST['producto_id'] ?? 0);
        $calificacion = (int)($_POST['calificacion'] ?? 0);
        $comentario = trim($_POST['comentario'] ?? '');

        $repo = new ResenaRepository();

        if ($calificacion < 1 || $calificacion > 5) {
            echo json_encode(['ok' => false, 'error' => 'La calificación debe estar entre 1 y 5 estrellas']);
            exit;
        }
        if (strlen($comentario) < 3) {
            echo json_encode(['ok' => false, 'error' => 'Escribe un comentario de al menos 3 caracteres']);
            exit;
        }
        if (!$repo->compro(Auth::id(), $productoId)) {
            echo json_encode(['ok' => false, 'error' => 'Solo puedes reseñar productos que hayas comprado']);
            exit;
        }
        if ($repo->yaResenio(Auth::id(), $productoId)) {
            echo json_encode(['ok' => false, 'error' => 'Ya reseñaste este producto']);
            exit;
        }

        $ok = $repo->crear(
            $productoId,
            Auth::id(),
            Auth::nombre(),
            $calificacion,
            $comentario
        );

        echo json_encode($ok
            ? ['ok' => true, 'mensaje' => 'Gracias por tu reseña']
            : ['ok' => false, 'error' => 'No se pudo guardar la reseña']);
    }
}
