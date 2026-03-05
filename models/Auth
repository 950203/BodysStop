<?php

require_once __DIR__ . '/../config/Database.php';

class ProductoModel
{
    private $db;

    public function __construct()
    {
        // Obtenemos la conexión PDO de la clase Database
        $this->db = Database::getConexion();
    }

    public function all()
    {
        // En PDO, query() devuelve un objeto que se puede iterar
        return $this->db->query("SELECT * FROM productos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO productos (nombre, precio, imagen) VALUES (?, ?, ?)");
        return $stmt->execute([$data['nombre'], $data['precio'], $data['imagen']]);
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE productos SET nombre=?, precio=?, imagen=? WHERE id=?");
        return $stmt->execute([$data['nombre'], $data['precio'], $data['imagen'], $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM productos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}