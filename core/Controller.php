<?php

class Controller
{
    protected function view(string $ruta, array $data = [])
    {
        extract($data);
        require "../views/$ruta.php";
    }
}