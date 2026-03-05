<?php

class PedidoRepository
{
    private $db;

    public function __construct()
    {
        $this->db = new PDO(
            "mysql:host=host.docker.internal;dbname=bodyshop;charset=utf8",
            "root",
            "root",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function guardarPedido($cliente, $cart)
    {
        try {
            $this->db->beginTransaction();

            $total = 0;
            foreach ($cart as $item) {
                $total += $item['subtotal'];
            }

            // Pedido
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

            // Detalle
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
