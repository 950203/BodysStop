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
            "SELECT p.*, c.nombre AS categoria_nombre
             FROM productos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
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

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO productos (nombre, descripcion, precio, imagen, categoria_id)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?? '',
            $data['precio'],
            $data['imagen'],
            $data['categoria_id'] !== '' ? $data['categoria_id'] : null,
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE productos SET nombre=?, descripcion=?, precio=?, imagen=?, categoria_id=? WHERE id=?"
        );
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?? '',
            $data['precio'],
            $data['imagen'],
            $data['categoria_id'] !== '' ? $data['categoria_id'] : null,
            $id,
        ]);
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
}
