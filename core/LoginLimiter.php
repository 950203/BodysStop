<?php

require_once BASE_PATH . '/config/Database.php';

class LoginLimiter
{
    private const MAX_INTENTOS = 5;
    private const BLOQUEO_MINUTOS = 15;

    public static function estaBloqueado(string $email): bool
    {
        $db = Database::getConexion();
        $stmt = $db->prepare(
            "SELECT bloqueado_hasta FROM login_intentos WHERE email = ? AND ip = ?"
        );
        $stmt->execute([$email, Security::ip()]);
        $hasta = $stmt->fetchColumn();

        if ($hasta && strtotime($hasta) > time()) {
            return true;
        }

        return false;
    }

    public static function restanteSegundos(string $email): int
    {
        $db = Database::getConexion();
        $stmt = $db->prepare(
            "SELECT bloqueado_hasta FROM login_intentos WHERE email = ? AND ip = ?"
        );
        $stmt->execute([$email, Security::ip()]);
        $hasta = $stmt->fetchColumn();

        if (!$hasta) {
            return 0;
        }

        return max(0, strtotime($hasta) - time());
    }

    public static function registrarFallo(string $email): void
    {
        $db = Database::getConexion();

        if (self::estaBloqueado($email)) {
            return;
        }

        $stmt = $db->prepare(
            "SELECT id, intentos FROM login_intentos WHERE email = ? AND ip = ?"
        );
        $stmt->execute([$email, Security::ip()]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fila) {
            $intentos = (int)$fila['intentos'] + 1;
            if ($intentos >= self::MAX_INTENTOS) {
                $bloqueo = date('Y-m-d H:i:s', time() + self::BLOQUEO_MINUTOS * 60);
                $upd = $db->prepare(
                    "UPDATE login_intentos SET intentos = ?, bloqueado_hasta = ? WHERE id = ?"
                );
                $upd->execute([$intentos, $bloqueo, $fila['id']]);
            } else {
                $upd = $db->prepare("UPDATE login_intentos SET intentos = ? WHERE id = ?");
                $upd->execute([$intentos, $fila['id']]);
            }
        } else {
            $ins = $db->prepare(
                "INSERT INTO login_intentos (email, ip, intentos) VALUES (?, ?, 1)"
            );
            $ins->execute([$email, Security::ip()]);
        }
    }

    public static function limpiar(string $email): void
    {
        $db = Database::getConexion();
        $stmt = $db->prepare("DELETE FROM login_intentos WHERE email = ? AND ip = ?");
        $stmt->execute([$email, Security::ip()]);
    }
}
