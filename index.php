<?php
session_start();
define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/core/Autoload.php';

$router = new Router();
$router->run();
