<?php
 
class Database
{
    private static ?PDO $db = null;
 
    public static function getConexion(): PDO
    {
        if (self::$db === null) {
            $host = getenv('DB_HOST') ?: 'localhost';
            $dbname = getenv('DB_NAME') ?: 'bodyshop';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') ?: '';
            self::$db = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        return self::$db;
    }
}