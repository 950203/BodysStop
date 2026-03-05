<?php

class CheckoutController
{
    public function index()
    {

        if (empty($_SESSION['cart'])) {
            header('Location: /');
            exit;
        }

        require BASE_PATH . '/views/checkout/index.php';
    }

    public function process()
    {

        // Aquí luego guardamos en DB
        $_SESSION['cart'] = [];

        echo json_encode(['ok' => true]);
    }
}
