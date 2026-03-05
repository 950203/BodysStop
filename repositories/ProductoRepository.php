<?php

class ProductoRepository
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
