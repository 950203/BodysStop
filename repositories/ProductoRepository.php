<?php

require_once __DIR__ . '/../config/Database.php';

class ProductoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConexion();
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM productos ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    // Catálogo con búsqueda, filtro por categoría, por talla y paginación
    public function buscar(string $busqueda = '', ?int $categoriaId = null, int $pagina = 1, int $porPagina = 8, ?string $talla = null): array
    {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre
                FROM productos p
                LEFT JOIN categorias c ON c.id = p.categoria_id
                WHERE p.activo = 1";
        $params = [];

        if ($busqueda !== '') {
            $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
            $like = '%' . $busqueda . '%';
            $params[] = $like;
            $params[] = $like;
        }

        if ($categoriaId !== null) {
            $sql .= " AND p.categoria_id = ?";
            $params[] = $categoriaId;
        }

        if ($talla !== null && $talla !== '') {
            $sql .= " AND p.id IN (
                SELECT DISTINCT pt.producto_id
                FROM producto_tallas pt
                WHERE pt.talla = ? AND pt.stock > 0
            )";
            $params[] = $talla;
        }

        $countSql = str_replace("SELECT p.*, c.nombre AS categoria_nombre", "SELECT COUNT(*)", $sql);
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        $offset = max(0, ($pagina - 1) * $porPagina);
        $sql .= " ORDER BY p.id DESC LIMIT $porPagina OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Adjuntar tallas con stock a cada producto
        foreach ($productos as &$p) {
            $p['tallas'] = $this->tallas((int)$p['id']);
        }

        return [
            'productos' => $productos,
            'total' => $total,
            'paginas' => (int)ceil($total / $porPagina),
            'pagina' => $pagina,
        ];
    }

    public function categorias(): array
    {
        $stmt = $this->db->query(
            "SELECT c.id, c.nombre, COUNT(p.id) AS total_productos
             FROM categorias c
             LEFT JOIN productos p ON p.categoria_id = c.id AND p.activo = 1
             GROUP BY c.id, c.nombre
             ORDER BY c.nombre"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tallas(int $productoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, talla, stock FROM producto_tallas WHERE producto_id = ? ORDER BY FIELD(talla, 'XS','S','M','L','XL','XXL'), talla"
        );
        $stmt->execute([$productoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function stockDe(int $productoId, string $talla): int
    {
        $stmt = $this->db->prepare(
            "SELECT stock FROM producto_tallas WHERE producto_id = ? AND talla = ?"
        );
        $stmt->execute([$productoId, $talla]);
        return (int)$stmt->fetchColumn();
    }

    public function reducirStock(int $productoId, string $talla, int $cantidad): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE producto_tallas SET stock = stock - ? WHERE producto_id = ? AND talla = ? AND stock >= ?"
        );
        $stmt->execute([$cantidad, $productoId, $talla, $cantidad]);
        return $stmt->rowCount() > 0;
    }
}
