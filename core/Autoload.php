<?php

spl_autoload_register(function ($class) {
    $dirs = [
        BASE_PATH . '/controllers/',
        BASE_PATH . '/models/',
        BASE_PATH . '/repositories/',
        BASE_PATH . '/core/',
        BASE_PATH . '/config/',
    ];

    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
