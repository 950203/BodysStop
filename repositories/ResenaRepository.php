<?php

require_once __DIR__ . '/../config/Database.php';

class ResenaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConexion();
    }

    public function porProducto(int $productoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM resenas WHERE producto_id = ? ORDER BY id DESC"
        );
        $stmt->execute([$productoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function promedio(int $productoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(AVG(calificacion), 0) AS promedio,
                    COUNT(*) AS total
             FROM resenas WHERE producto_id = ?"
        );
        $stmt->execute([$productoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ¿El usuario compró este producto (pedido no cancelado)?
    public function compro(int $usuarioId, int $productoId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM pedido_detalle pd
             JOIN pedidos p ON p.id = pd.pedido_id
             WHERE p.usuario_id = ? AND pd.producto_id = ? AND p.estado != 'cancelado'"
        );
        $stmt->execute([$usuarioId, $productoId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ¿El usuario ya reseñó este producto?
    public function yaResenio(int $usuarioId, int $productoId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM resenas WHERE usuario_id = ? AND producto_id = ?"
        );
        $stmt->execute([$usuarioId, $productoId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function crear(int $productoId, ?int $usuarioId, string $nombre, int $calificacion, string $comentario): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO resenas (producto_id, usuario_id, nombre, calificacion, comentario)
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$productoId, $usuarioId, $nombre, $calificacion, $comentario]);
    }
}
