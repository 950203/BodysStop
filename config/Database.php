<?php
 
class Database
{
    private static ?PDO $db = null;
 
    public static function getConexion(): PDO
    {
        if (self::$db === null) {
            self::$db = new PDO(
                "mysql:host=host.docker.internal;dbname=bodyshop;charset=utf8",
                "root",
                "root",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        return self::$db;
    }
}