<?php

require_once __DIR__ . '/../config/Database.php';

class UsuarioRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConexion();
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT id, nombre, marca, email, rol, activo, created_at FROM usuarios ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allPag(int $pagina = 1, int $porPagina = 10): array
    {
        $total = (int)$this->db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        $offset = max(0, ($pagina - 1) * $porPagina);

        $stmt = $this->db->prepare(
            "SELECT id, nombre, marca, email, rol, activo, created_at
             FROM usuarios ORDER BY id DESC LIMIT $porPagina OFFSET $offset"
        );
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'usuarios' => $usuarios,
            'total' => $total,
            'paginas' => (int)ceil($total / $porPagina),
            'pagina' => $pagina,
        ];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    public function emailExiste(string $email, ?int $exceptoId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
        $params = [$email];
        if ($exceptoId !== null) {
            $sql .= " AND id != ?";
            $params[] = $exceptoId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function cedulaExiste(string $cedula, ?int $exceptoId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE cedula = ?";
        $params = [$cedula];
        if ($exceptoId !== null) {
            $sql .= " AND id != ?";
            $params[] = $exceptoId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO usuarios (nombre, marca, cedula, email, password_hash, rol) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['nombre'],
            $data['marca'] ?? null,
            $data['cedula'] ?? '',
            $data['email'],
            $data['password_hash'],
            $data['rol'] ?? 'usuario',
        ]);
        return (int)$this->db->lastInsertId();
    }

    // Vendedores activos con su marca (para el formulario de productos)
    public function vendedores(): array
    {
        $stmt = $this->db->query(
            "SELECT id, nombre, marca FROM usuarios WHERE rol = 'vendedor' AND activo = 1 ORDER BY nombre"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function vendedor(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, nombre, marca FROM usuarios WHERE id = ? AND rol = 'vendedor'");
        $stmt->execute([$id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
        return $stmt->execute([$data['nombre'], $data['email'], $id]);
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    public function setActivo(int $id, bool $activo): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
        return $stmt->execute([$activo ? 1 : 0, $id]);
    }

    public function setRol(int $id, string $rol): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
        return $stmt->execute([$rol, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
