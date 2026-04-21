<?php
declare(strict_types=1);

define('BASE_PATH', __DIR__);

require BASE_PATH . '/app/core/bootstrap.php';

$router = new Router();
require BASE_PATH . '/app/config/routes.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($uri, $method);
