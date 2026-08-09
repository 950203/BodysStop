<?php
session_start();
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/Autoload.php';

echo "<h1>Diagnóstico BD BodyStop</h1>";

try {
    $db = Database::getConexion();
    echo "<p style='color:green'>✔ Conexión a MySQL exitosa</p>";

    $dbname = getenv('DB_NAME') ?: 'bodystop';

    // Mostrar tablas existentes
    $tables = $db->query("SHOW TABLES FROM `$dbname`")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h2>Tablas en '$dbname':</h2>";
    if (empty($tables)) {
        echo "<p style='color:orange'>No hay tablas. Creándolas...</p>";
        crearTablas($db);
    } else {
        echo "<ul>";
        foreach ($tables as $t) {
            echo "<li>$t</li>";
        }
        echo "</ul>";

        // Mostrar estructura de cada tabla
        foreach ($tables as $t) {
            echo "<h3>Estructura de '$t':</h3><table border='1' cellpadding='5'>";
            echo "<tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>PK</th><th>Default</th></tr>";
            $cols = $db->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $c) {
                echo "<tr><td>{$c['Field']}</td><td>{$c['Type']}</td><td>{$c['Null']}</td><td>{$c['Key']}</td><td>{$c['Default']}</td></tr>";
            }
            echo "</table>";
        }

        // Verificar tablas necesarias
        $needed = ['productos', 'pedidos', 'pedido_detalle'];
        $missing = array_diff($needed, $tables);
        if (!empty($missing)) {
            echo "<p style='color:orange'>Faltan tablas: " . implode(', ', $missing) . ". Creándolas...</p>";
            crearTablas($db);
        } else {
            echo "<p style='color:green'>✔ Todas las tablas necesarias existen</p>";
            verificarColumnas($db);
        }
    }

} catch (Exception $e) {
    echo "<p style='color:red'>✘ Error: " . $e->getMessage() . "</p>";
}

function crearTablas(PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS productos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            precio DECIMAL(10,2) NOT NULL,
            imagen VARCHAR(500) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    $db->exec("
        CREATE TABLE IF NOT EXISTS pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_cliente VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            direccion TEXT NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    $db->exec("
        CREATE TABLE IF NOT EXISTS pedido_detalle (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pedido_id INT NOT NULL,
            producto_id INT NOT NULL,
            cantidad INT NOT NULL,
            precio DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
            FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    echo "<p style='color:green'>✔ Tablas creadas correctamente</p>";
}

function verificarColumnas(PDO $db) {
    $checks = [
        'productos' => ['id', 'nombre', 'precio', 'imagen'],
        'pedidos' => ['id', 'nombre_cliente', 'email', 'direccion', 'total'],
        'pedido_detalle' => ['id', 'pedido_id', 'producto_id', 'cantidad', 'precio', 'subtotal'],
    ];

    foreach ($checks as $table => $expected) {
        $cols = $db->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_COLUMN, 0);
        $missing = array_diff($expected, $cols);
        if (empty($missing)) {
            echo "<p style='color:green'>✔ Tabla '$table' tiene todas las columnas necesarias</p>";
        } else {
            echo "<p style='color:orange'>⚠ Tabla '$table' le falta: " . implode(', ', $missing) . "</p>";
        }
    }
}
