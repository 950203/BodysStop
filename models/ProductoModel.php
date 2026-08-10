<?php

require_once __DIR__ . '/../config/Database.php';

class ProductoModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConexion();
    }

    public function all()
    {
        return $this->db->query(
            "SELECT p.*, c.nombre AS categoria_nombre
             FROM productos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             ORDER BY p.id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allPag(int $pagina = 1, int $porPagina = 10): array
    {
        $total = (int)$this->db->query("SELECT COUNT(*) FROM productos")->fetchColumn();
        $offset = max(0, ($pagina - 1) * $porPagina);

        $stmt = $this->db->prepare(
            "SELECT p.*, c.nombre AS categoria_nombre,
                    COALESCE(SUM(pt.stock), 0) AS stock_total,
                    COALESCE(GROUP_CONCAT(CONCAT(pt.talla, ':', pt.stock) ORDER BY pt.talla SEPARATOR ','), '') AS stock_detalle
             FROM productos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             LEFT JOIN producto_tallas pt ON pt.producto_id = p.id
             GROUP BY p.id
             ORDER BY p.id DESC LIMIT $porPagina OFFSET $offset"
        );
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'productos' => $productos,
            'total' => $total,
            'paginas' => (int)ceil($total / $porPagina),
            'pagina' => $pagina,
        ];
    }

    // Productos de un vendedor con stock por talla (para la página de stock)
    public function allByVendedor(int $vendedorId, int $pagina = 1, int $porPagina = 10): array
    {
        $stmtTotal = $this->db->prepare("SELECT COUNT(*) FROM productos WHERE vendedor_id = ?");
        $stmtTotal->execute([$vendedorId]);
        $total = (int)$stmtTotal->fetchColumn();
        $offset = max(0, ($pagina - 1) * $porPagina);

        $stmt = $this->db->prepare(
            "SELECT p.*, c.nombre AS categoria_nombre,
                    COALESCE(SUM(pt.stock), 0) AS stock_total,
                    COALESCE(GROUP_CONCAT(CONCAT(pt.talla, ':', pt.stock) ORDER BY pt.talla SEPARATOR ','), '') AS stock_detalle
             FROM productos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             LEFT JOIN producto_tallas pt ON pt.producto_id = p.id
             WHERE p.vendedor_id = ?
             GROUP BY p.id
             ORDER BY p.id DESC LIMIT $porPagina OFFSET $offset"
        );
        $stmt->execute([$vendedorId]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'productos' => $productos,
            'total' => $total,
            'paginas' => (int)ceil($total / $porPagina),
            'pagina' => $pagina,
        ];
    }

    // ¿El producto pertenece al vendedor?
    public function perteneceA(int $productoId, int $vendedorId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM productos WHERE id = ? AND vendedor_id = ?");
        $stmt->execute([$productoId, $vendedorId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO productos (nombre, descripcion, material, precio, imagen, categoria_id, marca, vendedor_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?? '',
            $data['material'] ?? '',
            $data['precio'],
            $data['imagen'],
            $data['categoria_id'] !== '' ? $data['categoria_id'] : null,
            $data['marca'] ?? null,
            !empty($data['vendedor_id']) ? (int)$data['vendedor_id'] : null,
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE productos SET nombre=?, descripcion=?, material=?, precio=?, imagen=?, categoria_id=?, marca=?, vendedor_id=? WHERE id=?"
        );
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?? '',
            $data['material'] ?? '',
            $data['precio'],
            $data['imagen'],
            $data['categoria_id'] !== '' ? $data['categoria_id'] : null,
            $data['marca'] ?? null,
            !empty($data['vendedor_id']) ? (int)$data['vendedor_id'] : null,
            $id,
        ]);
    }

    // Vendedores activos con su marca (para el formulario de productos)
    public function vendedores(): array
    {
        return $this->db->query(
            "SELECT id, nombre, marca FROM usuarios WHERE rol = 'vendedor' AND activo = 1 ORDER BY nombre"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function vendedor(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, nombre, marca FROM usuarios WHERE id = ? AND rol = 'vendedor'");
        $stmt->execute([$id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    // Soft delete: el producto queda oculto en la tienda pero se conserva
    public function delete($id)
    {
        $stmt = $this->db->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function setActivo(int $id, bool $activo): bool
    {
        $stmt = $this->db->prepare("UPDATE productos SET activo = ? WHERE id = ?");
        return $stmt->execute([$activo ? 1 : 0, $id]);
    }

    public function categorias()
    {
        return $this->db->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tallas($productoId)
    {
        $stmt = $this->db->prepare("SELECT talla, stock FROM producto_tallas WHERE producto_id = ? ORDER BY talla");
        $stmt->execute([$productoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Reemplaza el stock por talla de un producto
    public function guardarTallas($productoId, array $tallas)
    {
        $stmtDel = $this->db->prepare("DELETE FROM producto_tallas WHERE producto_id = ?");
        $stmtDel->execute([$productoId]);

        $stmt = $this->db->prepare(
            "INSERT INTO producto_tallas (producto_id, talla, stock) VALUES (?, ?, ?)"
        );

        foreach ($tallas as $talla => $stock) {
            $talla = trim($talla);
            $stock = (int)$stock;
            if ($talla !== '' && $stock >= 0) {
                $stmt->execute([$productoId, $talla, $stock]);
            }
        }
    }

    // Actualiza el stock de una talla (la crea si no existe)
    public function actualizarStock(int $productoId, string $talla, int $stock): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO producto_tallas (producto_id, talla, stock) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE stock = VALUES(stock)"
        );
        return $stmt->execute([$productoId, $talla, $stock]);
    }
}
