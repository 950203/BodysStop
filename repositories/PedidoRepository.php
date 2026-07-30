<?php

require_once __DIR__ . '/../config/Database.php';

class PedidoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConexion();
    }

    public function guardarPedido($cliente, $cart)
    {
        try {
            $this->db->beginTransaction();

            $total = 0;
            foreach ($cart as $item) {
                $total += $item['subtotal'];
            }

            $stmt = $this->db->prepare(
                "INSERT INTO pedidos (nombre_cliente, email, direccion, total)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                $cliente['nombre'],
                $cliente['email'],
                $cliente['direccion'],
                $total
            ]);

            $pedidoId = $this->db->lastInsertId();

            $stmtDetalle = $this->db->prepare(
                "INSERT INTO pedido_detalle
                (pedido_id, producto_id, cantidad, precio, subtotal)
                VALUES (?, ?, ?, ?, ?)"
            );

            foreach ($cart as $item) {
                $stmtDetalle->execute([
                    $pedidoId,
                    $item['id'],
                    $item['qty'],
                    $item['precio'],
                    $item['subtotal']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
