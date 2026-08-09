<?php

require_once BASE_PATH . '/core/Auth.php';

// Compatibilidad: el antiguo acceso admin ahora redirige al login general
class AdminAuthController
{
    public function index()
    {
        if (Auth::check()) {
            Auth::redirigirPorRol(Auth::rol());
        }
        header('Location: /?c=Auth&m=login');
        exit;
    }
}
