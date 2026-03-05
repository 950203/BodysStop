<?php

class Router
{
    public function run()
    {
        $controller = $_GET['c'] ?? 'Home';
        $method     = $_GET['m'] ?? 'index';

        $controllerClass = $controller . 'Controller';
        $controllerFile  = BASE_PATH . '/controllers/' . $controllerClass . '.php';

        if (!file_exists($controllerFile)) {
            die("Controlador no encontrado: $controllerClass");
        }

        require_once $controllerFile;

        if (!class_exists($controllerClass)) {
            die("Clase $controllerClass no existe");
        }

        $instance = new $controllerClass();

        if (!method_exists($instance, $method)) {
            die("Método $method no encontrado");
        }

        call_user_func([$instance, $method]);
    }
}
