<?php

class Producto
{
    private $id;
    private $nombre;
    private $precio;
    private $imagen;

    public function __construct($id, $nombre, $precio, $imagen)
    {
        $this->id     = $id;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->imagen = $imagen;
    }

    public function getId()
    {
        return $this->id;
    }
    public function getNombre()
    {
        return $this->nombre;
    }
    public function getPrecio()
    {
        return $this->precio;
    }
    public function getImagen()
    {
        return $this->imagen;
    }
}
