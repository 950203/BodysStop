<?php

class ProductoController
{
    private $repo;

    public function __construct()
    {
        $this->repo = new ProductoRepository();
    }

    public function index()
    {
        $productos = $this->repo->all();
        require BASE_PATH . '/views/productos/index.php';
    }
}
