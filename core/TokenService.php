<?php

require_once BASE_PATH . '/config/Database.php';

class TokenService
{
    // Emite un token de acceso y lo guarda hasheado en la BD
    public static function emitir(int $usuarioId, string $tipo = 'api', int $vigenciaHoras = 1): array
    {
        $token = Security::generarToken();
        $expira = date('Y-m-d H:i:s', time() + $vigenciaHoras * 3600);

        $db = Database::getConexion();
        $stmt = $db->prepare(
            "INSERT INTO auth_tokens (usuario_id, token_hash, tipo, expira_en) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$usuarioId, Security::hashToken($token), $tipo, $expira]);

        return ['token' => $token, 'expira_en' => $expira];
    }

    // Valida un token en claro y devuelve sus datos (usuario, rol) o null
    public static function validar(?string $token): ?array
    {
        if (!$token || $token === '') {
            return null;
        }

        $db = Database::getConexion();
        $stmt = $db->prepare(
            "SELECT t.usuario_id, t.tipo, t.expira_en, u.rol, u.activo, u.nombre
             FROM auth_tokens t
             JOIN usuarios u ON u.id = t.usuario_id
             WHERE t.token_hash = ? AND t.revocado = 0
             LIMIT 1"
        );
        $stmt->execute([Security::hashToken($token)]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        if ((int)$fila['activo'] !== 1) {
            return null;
        }

        if ($fila['expira_en'] !== null && strtotime($fila['expira_en']) < time()) {
            self::revocar($token);
            return null;
        }

        return $fila;
    }

    public static function revocar(?string $token): void
    {
        if (!$token) {
            return;
        }

        $db = Database::getConexion();
        $stmt = $db->prepare("UPDATE auth_tokens SET revocado = 1 WHERE token_hash = ?");
        $stmt->execute([Security::hashToken($token)]);
    }

    public static function limpiarExpirados(): void
    {
        $db = Database::getConexion();
        $db->exec("UPDATE auth_tokens SET revocado = 1 WHERE expira_en IS NOT NULL AND expira_en < NOW()");
    }
}
