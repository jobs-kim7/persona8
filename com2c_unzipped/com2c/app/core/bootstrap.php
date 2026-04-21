<?php
declare(strict_types=1);

spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/controllers/' . $class . '.php',
        BASE_PATH . '/app/models/' . $class . '.php',
        BASE_PATH . '/app/services/' . $class . '.php',
        BASE_PATH . '/app/core/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require $path;
            return;
        }
    }
});

require BASE_PATH . '/app/core/helpers.php';
require BASE_PATH . '/app/core/db.php';
require BASE_PATH . '/app/core/router.php';
require BASE_PATH . '/app/core/controller.php';
require BASE_PATH . '/app/core/view.php';

session_start();

function config(string $name): array {
    return require BASE_PATH . '/app/config/' . $name . '.php';
}
