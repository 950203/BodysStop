<?php

use PSpell\Config;

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = conexion::getConexion();
    }
}
