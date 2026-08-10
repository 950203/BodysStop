<?php

require_once __DIR__ . '/../config/Database.php';

class CarritoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConexion();
    }

    // Guarda el carrito de la sesión en la BD (reemplaza el estado anterior)
    public function guardar(int $usuarioId, array $cart): void
    {
        $this->db->beginTransaction();

        $stmtDel = $this->db->prepare("DELETE FROM carrito_items WHERE usuario_id = ?");
        $stmtDel->execute([$usuarioId]);

        $stmtIns = $this->db->prepare(
            "INSERT INTO carrito_items (usuario_id, producto_id, talla, cantidad) VALUES (?, ?, ?, ?)"
        );

        foreach ($cart as $clave => $cantidad) {
            [$id, $talla] = array_pad(explode(':', (string)$clave, 2), 2, '');
            if ($id === '' || $talla === '') {
                continue;
            }
            $stmtIns->execute([$usuarioId, (int)$id, $talla, max(1, (int)$cantidad)]);
        }

        $this->db->commit();
    }

    // Carga el carrito guardado en la BD con el mismo formato que la sesión (id:talla => cantidad)
    public function cargar(int $usuarioId): array
    {
        $stmt = $this->db->prepare(
            "SELECT producto_id, talla, cantidad FROM carrito_items WHERE usuario_id = ?"
        );
        $stmt->execute([$usuarioId]);

        $cart = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $cart[$fila['producto_id'] . ':' . $fila['talla']] = (int)$fila['cantidad'];
        }
        return $cart;
    }

    public function vaciar(int $usuarioId): void
    {
        $stmt = $this->db->prepare("DELETE FROM carrito_items WHERE usuario_id = ?");
        $stmt->execute([$usuarioId]);
    }
}
