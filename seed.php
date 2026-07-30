<?php
session_start();
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/Autoload.php';

try {
    $db = Database::getConexion();

    $count = $db->query("SELECT COUNT(*) FROM productos")->fetchColumn();

    if ($count > 0) {
        echo "Ya hay $count productos en la BD. No se agregaron duplicados.";
        exit;
    }

    $productos = [
        ['Body Encaje Negro', 45000, 'https://images.unsplash.com/photo-1520975916090-3105956dac38?w=400&h=500&fit=crop'],
        ['Body Clásico Blanco', 42000, 'https://images.unsplash.com/photo-1520975916090-3105956dac38?w=400&h=500&fit=crop'],
        ['Body Deportivo Rojo', 48000, 'https://images.unsplash.com/photo-1520975916090-3105956dac38?w=400&h=500&fit=crop'],
        ['Body Tul Rosado', 52000, 'https://images.unsplash.com/photo-1520975916090-3105956dac38?w=400&h=500&fit=crop'],
        ['Body Vintage Azul', 47000, 'https://images.unsplash.com/photo-1520975916090-3105956dac38?w=400&h=500&fit=crop'],
        ['Body Satín Dorado', 55000, 'https://images.unsplash.com/photo-1520975916090-3105956dac38?w=400&h=500&fit=crop'],
    ];

    $stmt = $db->prepare("INSERT INTO productos (nombre, precio, imagen) VALUES (?, ?, ?)");

    foreach ($productos as $p) {
        $stmt->execute($p);
    }

    echo "✔ " . count($productos) . " productos insertados correctamente.";
} catch (Exception $e) {
    echo "✘ Error: " . $e->getMessage();
}
