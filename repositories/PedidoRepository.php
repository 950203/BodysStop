<?php

require_once __DIR__ . '/../config/Database.php';

class PedidoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConexion();
    }

    public function guardarPedido($cliente, $cart, ?int $usuarioId = null)
    {
        try {
            $this->db->beginTransaction();

            // Validar y descontar stock antes de guardar
            $stmtStock = $this->db->prepare(
                "UPDATE producto_tallas SET stock = stock - ?
                 WHERE producto_id = ? AND talla = ? AND stock >= ?"
            );

            $total = 0;
            foreach ($cart as $item) {
                $total += $item['subtotal'];

                $talla = $item['talla'] ?? '';
                if ($talla === '') {
                    throw new Exception('Falta la talla del producto');
                }

                $stmtStock->execute([
                    (int)$item['qty'],
                    (int)$item['id'],
                    $talla,
                    (int)$item['qty'],
                ]);
                if ($stmtStock->rowCount() === 0) {
                    throw new Exception('No hay suficiente stock de ' . ($item['nombre'] ?? 'producto') . ' en talla ' . $talla);
                }
            }

            $stmt = $this->db->prepare(
                "INSERT INTO pedidos (usuario_id, nombre_cliente, email, direccion, total)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $usuarioId,
                $cliente['nombre'],
                $cliente['email'],
                $cliente['direccion'],
                $total
            ]);

            $pedidoId = $this->db->lastInsertId();

            $stmtDetalle = $this->db->prepare(
                "INSERT INTO pedido_detalle
                (pedido_id, producto_id, talla, cantidad, precio, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            foreach ($cart as $item) {
                $stmtDetalle->execute([
                    $pedidoId,
                    $item['id'],
                    $item['talla'],
                    $item['qty'],
                    $item['precio'],
                    $item['subtotal']
                ]);
            }

            $this->db->commit();
            return ['ok' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // Pedidos de un usuario con sus detalles
    public function porUsuario(int $usuarioId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY id DESC"
        );
        $stmt->execute([$usuarioId]);

        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtDetalle = $this->db->prepare(
            "SELECT pd.*, p.nombre AS producto_nombre, p.imagen AS producto_imagen
             FROM pedido_detalle pd
             JOIN productos p ON p.id = pd.producto_id
             WHERE pd.pedido_id = ?"
        );

        foreach ($pedidos as &$pedido) {
            $stmtDetalle->execute([$pedido['id']]);
            $pedido['detalle'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
        }

        return $pedidos;
    }

    // Todos los pedidos con filtro opcional por estado/búsqueda y paginación
    public function todos(?string $estado = null, string $busqueda = '', int $pagina = 1, int $porPagina = 10): array
    {
        $sql = "FROM pedidos WHERE 1=1";
        $params = [];

        if ($estado !== null && $estado !== '') {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }

        if ($busqueda !== '') {
            $sql .= " AND (nombre_cliente LIKE ? OR email LIKE ? OR id LIKE ?)";
            $busqueda = '%' . $busqueda . '%';
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
        }

        $stmtCount = $this->db->prepare("SELECT COUNT(*) " . $sql);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        $offset = max(0, ($pagina - 1) * $porPagina);
        $stmt = $this->db->prepare("SELECT * " . $sql . " ORDER BY id DESC LIMIT $porPagina OFFSET $offset");
        $stmt->execute($params);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'pedidos' => $pedidos,
            'total' => $total,
            'paginas' => (int)ceil($total / $porPagina),
            'pagina' => $pagina,
        ];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM pedidos WHERE id = ?");
        $stmt->execute([$id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            return null;
        }

        $stmtDetalle = $this->db->prepare(
            "SELECT pd.*, p.nombre AS producto_nombre, p.imagen AS producto_imagen
             FROM pedido_detalle pd
             JOIN productos p ON p.id = pd.producto_id
             WHERE pd.pedido_id = ?"
        );
        $stmtDetalle->execute([$id]);
        $pedido['detalle'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

        return $pedido;
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }

    // Métricas para el dashboard
    public function metricas(): array
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total_pedidos,
                    COALESCE(SUM(CASE WHEN estado != 'cancelado' THEN total ELSE 0 END), 0) AS ingresos,
                    COUNT(DISTINCT usuario_id) AS clientes,
                    COUNT(DISTINCT CASE WHEN estado != 'cancelado' AND estado != 'pendiente' THEN id END) AS completados
             FROM pedidos"
        );
        $metricas = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmtEstado = $this->db->query(
            "SELECT estado, COUNT(*) AS cantidad FROM pedidos GROUP BY estado"
        );
        $metricas['por_estado'] = $stmtEstado->fetchAll(PDO::FETCH_ASSOC);

        return $metricas;
    }

    public function masVendidos(int $limite = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.id, p.nombre, p.imagen, p.precio, SUM(pd.cantidad) AS vendidos
             FROM pedido_detalle pd
             JOIN pedidos pe ON pe.id = pd.pedido_id
             JOIN productos p ON p.id = pd.producto_id
             WHERE pe.estado != 'cancelado'
             GROUP BY p.id, p.nombre, p.imagen, p.precio
             ORDER BY vendidos DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
