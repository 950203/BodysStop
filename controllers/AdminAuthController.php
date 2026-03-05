<?php

class AdminAuthController
{
    private $model;

    public function __construct()
    {
        $this->model = new Auth(2569,"Admin","admin@gmail.com","2693");
    }

    public function index()
    {
        require BASE_PATH . '/views/admin/auth/index.php';
    }
}

class Auth
{
    private $id;
    private $nombre_completo;
    private $correo;
    private $contraseña;

    public function __construct($id, $nombre_completo, $correo, $contraseña)
    {
        $this->id = $id;
        $this->nombre_completo = $nombre_completo;
        $this->correo = $correo;
        $this->contraseña = $contraseña;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNombreCompleto()
    {
        return $this->nombre_completo;
    }

    public function getCorreo()
    {
        return $this->correo;
    }

    public function getContraseña()
    {
        return $this->contraseña;
    }
}