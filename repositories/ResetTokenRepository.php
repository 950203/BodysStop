<?php

require_once __DIR__ . '/../config/Database.php';

class ResetTokenRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConexion();
    }

    public function crear(int $usuarioId, string $tokenHash, int $vigenciaMinutos = 30): bool
    {
        $this->invalidarDe($usuarioId);

        $stmt = $this->db->prepare(
            "INSERT INTO password_reset_tokens (usuario_id, token_hash, expira_en)
             VALUES (?, ?, ?)"
        );
        return $stmt->execute([
            $usuarioId,
            $tokenHash,
            date('Y-m-d H:i:s', time() + $vigenciaMinutos * 60),
        ]);
    }

    public function validar(string $tokenHash): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM password_reset_tokens
             WHERE token_hash = ? AND usado = 0 AND expira_en > NOW()
             LIMIT 1"
        );
        $stmt->execute([$tokenHash]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    public function marcarUsado(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE password_reset_tokens SET usado = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function invalidarDe(int $usuarioId): void
    {
        $stmt = $this->db->prepare("UPDATE password_reset_tokens SET usado = 1 WHERE usuario_id = ? AND usado = 0");
        $stmt->execute([$usuarioId]);
    }

    public function limpiarExpirados(): void
    {
        $this->db->exec("DELETE FROM password_reset_tokens WHERE expira_en < NOW()");
    }
}
