<?php

require_once __DIR__ . '/../config/Database.php';

class ProductoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConexion();
    }

    public function all()
    {
        $stmt = $this->db->query("SELECT * FROM productos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
