<?php

require_once __DIR__ . '/../config/Database.php';

class PedidoRepository
{
    private PDO $db;

    // Días estimados para la entrega de un pedido (simulado)
    private const DIAS_ENTREGA_ESTIMADA = 3;

    // Punto de origen de los envíos (simulado)
    private const ORIGEN_ENVIO = 'Medellín, Colombia';

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
                "INSERT INTO pedidos (usuario_id, nombre_cliente, email, direccion, metodo_pago, fecha_estimada_entrega, total)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $usuarioId,
                $cliente['nombre'],
                $cliente['email'],
                $cliente['direccion'],
                $cliente['metodo_pago'] ?? '',
                date('Y-m-d H:i:s', time() + self::DIAS_ENTREGA_ESTIMADA * 24 * 3600),
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

            $this->agregarEvento(
                $pedidoId,
                'Pedido realizado',
                self::ORIGEN_ENVIO,
                'Recibimos tu pedido. Estamos preparándolo para el envío.'
            );

            $this->db->commit();
            return ['ok' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // Registra un evento de seguimiento para un pedido
    public function agregarEvento(int $pedidoId, string $titulo, ?string $ubicacion = null, ?string $detalle = null): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO seguimiento_pedidos (pedido_id, titulo, ubicacion, detalle) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$pedidoId, $titulo, $ubicacion, $detalle]);
    }

    // Eventos de seguimiento de un pedido (de más antiguo a más reciente)
    public function seguimiento(int $pedidoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM seguimiento_pedidos WHERE pedido_id = ? ORDER BY id ASC"
        );
        $stmt->execute([$pedidoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            $pedido['seguimiento'] = $this->seguimiento($pedido['id']);
        }

        return $pedidos;
    }

    // Todos los pedidos con filtro opcional por estado/búsqueda y paginación.
    // Si $vendedorId se indica, solo se muestran pedidos que contienen productos de ese vendedor.
    public function todos(?string $estado = null, string $busqueda = '', int $pagina = 1, int $porPagina = 10, ?int $vendedorId = null): array
    {
        $sql = "FROM pedidos WHERE 1=1";
        $params = [];

        if ($vendedorId !== null) {
            $sql .= " AND id IN (
                SELECT DISTINCT pd.pedido_id
                FROM pedido_detalle pd
                JOIN productos p ON p.id = pd.producto_id
                WHERE p.vendedor_id = ?
            )";
            $params[] = $vendedorId;
        }

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
            "SELECT pd.*, p.nombre AS producto_nombre, p.imagen AS producto_imagen,
                    p.marca AS producto_marca, p.vendedor_id AS producto_vendedor_id
             FROM pedido_detalle pd
             JOIN productos p ON p.id = pd.producto_id
             WHERE pd.pedido_id = ?"
        );
        $stmtDetalle->execute([$id]);
        $pedido['detalle'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

        return $pedido;
    }

    // Historial de ventas: todos los pedidos (no cancelados) con sus productos.
    // Si $vendedorId se indica, solo se muestran las ventas de ese vendedor:
    // los pedidos que contienen productos suyos, mostrando únicamente sus líneas.
    public function historialVentas(?int $vendedorId = null): array
    {
        $sql = "SELECT * FROM pedidos WHERE estado != 'cancelado'";
        $params = [];

        if ($vendedorId !== null) {
            $sql .= " AND id IN (
                SELECT DISTINCT pd.pedido_id
                FROM pedido_detalle pd
                JOIN productos p ON p.id = pd.producto_id
                WHERE p.vendedor_id = ?
            )";
            $params[] = $vendedorId;
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtDetalle = $this->db->prepare(
            "SELECT pd.*, p.nombre AS producto_nombre, p.imagen AS producto_imagen,
                    p.marca AS producto_marca, p.vendedor_id AS producto_vendedor_id
             FROM pedido_detalle pd
             JOIN productos p ON p.id = pd.producto_id
             WHERE pd.pedido_id = ?
             ORDER BY pd.id ASC"
        );

        foreach ($pedidos as &$pedido) {
            $stmtDetalle->execute([$pedido['id']]);
            $detalle = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

            if ($vendedorId !== null) {
                $detalle = array_values(array_filter(
                    $detalle,
                    fn($d) => (int)$d['producto_vendedor_id'] === $vendedorId
                ));
            }

            $pedido['detalle'] = $detalle;

            // Marcas presentes en la venta (columna Vendedor)
            $marcas = array_filter(array_map(fn($d) => trim($d['producto_marca'] ?? ''), $detalle));
            $pedido['marcas'] = array_values(array_unique($marcas));
        }

        return $pedidos;
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        if (!$stmt->execute([$estado, $id])) {
            return false;
        }

        $pedido = $this->find($id);
        $origen = self::ORIGEN_ENVIO;

        switch ($estado) {
            case 'pagado':
                $this->agregarEvento(
                    $id,
                    'Pago confirmado',
                    $origen,
                    'Confirmamos tu pago. Tu pedido está siendo preparado y empacado.'
                );
                break;
            case 'en_camino':
                $this->agregarEvento(
                    $id,
                    'En camino',
                    $origen,
                    'Tu pedido salió del centro de distribución y está en ruta hacia tu dirección.'
                );
                if (!empty($pedido['direccion'])) {
                    $this->agregarEvento(
                        $id,
                        'En tránsito',
                        'En ruta a tu ciudad',
                        'El paquete avanzó por la ruta de reparto. Destino: ' . $pedido['direccion']
                    );
                }
                break;
            case 'entregado':
                $this->agregarEvento(
                    $id,
                    'Entregado',
                    'Tu dirección',
                    'Tu pedido fue entregado. ¡Gracias por comprar en BodyStop!'
                );
                break;
            case 'cancelado':
                $this->agregarEvento(
                    $id,
                    'Pedido cancelado',
                    null,
                    'Tu pedido fue cancelado.'
                );
                break;
        }

        return true;
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

    // Filtro SQL de rango de fechas sobre pedidos (alias pe)
    private function rangoFechas(?string $desde, ?string $hasta): array
    {
        $sql = '';
        $params = [];
        if ($desde !== null && $desde !== '') {
            $sql .= ' AND pe.created_at >= ?';
            $params[] = $desde . ' 00:00:00';
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= ' AND pe.created_at <= ?';
            $params[] = $hasta . ' 23:59:59';
        }
        return [$sql, $params];
    }

    // Resumen general para reportes (con rango de fechas opcional)
    public function reporteResumen(?string $desde = null, ?string $hasta = null): array
    {
        [$filtro, $params] = $this->rangoFechas($desde, $hasta);

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total_pedidos,
                    COALESCE(SUM(CASE WHEN pe.estado != 'cancelado' THEN pe.total ELSE 0 END), 0) AS ingresos,
                    COALESCE(SUM(CASE WHEN pe.estado != 'cancelado' THEN 1 ELSE 0 END), 0) AS pedidos_validos,
                    COALESCE(SUM(CASE WHEN pe.estado = 'cancelado' THEN 1 ELSE 0 END), 0) AS cancelados
             FROM pedidos pe WHERE 1=1" . $filtro
        );
        $stmt->execute($params);
        $resumen = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmtU = $this->db->prepare(
            "SELECT COALESCE(SUM(pd.cantidad), 0) AS unidades
             FROM pedido_detalle pd
             JOIN pedidos pe ON pe.id = pd.pedido_id
             WHERE pe.estado != 'cancelado'" . $filtro
        );
        $stmtU->execute($params);
        $resumen['unidades'] = (int)$stmtU->fetchColumn();

        $resumen['ingresos'] = (float)$resumen['ingresos'];
        $resumen['total_pedidos'] = (int)$resumen['total_pedidos'];
        $resumen['pedidos_validos'] = (int)$resumen['pedidos_validos'];
        $resumen['cancelados'] = (int)$resumen['cancelados'];

        return $resumen;
    }

    // Ventas por día
    public function reporteVentasPorDia(?string $desde = null, ?string $hasta = null): array
    {
        [$filtro, $params] = $this->rangoFechas($desde, $hasta);

        $sql = "SELECT DATE(pe.created_at) AS fecha,
                       COUNT(*) AS pedidos,
                       COALESCE(SUM(pe.total), 0) AS ingresos
                FROM pedidos pe
                WHERE pe.estado != 'cancelado'" . $filtro . "
                GROUP BY DATE(pe.created_at)
                ORDER BY fecha ASC";

        if (($desde === null || $desde === '') && ($hasta === null || $hasta === '')) {
            $sql .= " LIMIT 30";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ventas por producto
    public function reporteVentasPorProducto(?string $desde = null, ?string $hasta = null): array
    {
        [$filtro, $params] = $this->rangoFechas($desde, $hasta);

        $stmt = $this->db->prepare(
            "SELECT p.id, p.nombre, p.imagen, p.precio,
                    SUM(pd.cantidad) AS unidades,
                    COALESCE(SUM(pd.subtotal), 0) AS ingresos
             FROM pedido_detalle pd
             JOIN pedidos pe ON pe.id = pd.pedido_id
             JOIN productos p ON p.id = pd.producto_id
             WHERE pe.estado != 'cancelado'" . $filtro . "
             GROUP BY p.id, p.nombre, p.imagen, p.precio
             ORDER BY unidades DESC, ingresos DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ventas por categoría
    public function reporteVentasPorCategoria(?string $desde = null, ?string $hasta = null): array
    {
        [$filtro, $params] = $this->rangoFechas($desde, $hasta);

        $stmt = $this->db->prepare(
            "SELECT COALESCE(c.nombre, 'Sin categoría') AS categoria,
                    SUM(pd.cantidad) AS unidades,
                    COALESCE(SUM(pd.subtotal), 0) AS ingresos
             FROM pedido_detalle pd
             JOIN pedidos pe ON pe.id = pd.pedido_id
             LEFT JOIN productos p ON p.id = pd.producto_id
             LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE pe.estado != 'cancelado'" . $filtro . "
             GROUP BY categoria
             ORDER BY ingresos DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ventas por método de pago
    public function reporteVentasPorMetodo(?string $desde = null, ?string $hasta = null): array
    {
        [$filtro, $params] = $this->rangoFechas($desde, $hasta);

        $stmt = $this->db->prepare(
            "SELECT pe.metodo_pago, COUNT(*) AS pedidos, COALESCE(SUM(pe.total), 0) AS ingresos
             FROM pedidos pe
             WHERE pe.estado != 'cancelado'
               AND pe.metodo_pago IS NOT NULL
               AND pe.metodo_pago != ''" . $filtro . "
             GROUP BY pe.metodo_pago
             ORDER BY ingresos DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Pedidos por estado
    public function reporteVentasPorEstado(?string $desde = null, ?string $hasta = null): array
    {
        [$filtro, $params] = $this->rangoFechas($desde, $hasta);

        $stmt = $this->db->prepare(
            "SELECT pe.estado, COUNT(*) AS pedidos, COALESCE(SUM(pe.total), 0) AS ingresos
             FROM pedidos pe WHERE 1=1" . $filtro . "
             GROUP BY pe.estado
             ORDER BY pedidos DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Top clientes por gasto
    public function reporteTopClientes(?string $desde = null, ?string $hasta = null, int $limite = 5): array
    {
        [$filtro, $params] = $this->rangoFechas($desde, $hasta);
        $params[] = $limite;

        $sql = "SELECT pe.usuario_id,
                       MAX(pe.nombre_cliente) AS nombre,
                       MAX(pe.email) AS email,
                       COUNT(*) AS pedidos,
                       COALESCE(SUM(pe.total), 0) AS total
                FROM pedidos pe
                WHERE pe.estado != 'cancelado'
                  AND pe.usuario_id IS NOT NULL" . $filtro . "
                GROUP BY pe.usuario_id
                ORDER BY total DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $i => $p) {
            $stmt->bindValue($i + 1, $p, is_int($p) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
