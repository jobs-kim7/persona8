<?php
class Router {
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $uri, string $action): void {
        $this->routes['GET'][$uri] = $action;
    }

    public function post(string $uri, string $action): void {
        $this->routes['POST'][$uri] = $action;
    }

    public function dispatch(string $uri, string $method): void {
        $action = $this->routes[$method][$uri] ?? null;
        if (!$action) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        [$controller, $handler] = explode('@', $action);
        $instance = new $controller();
        $instance->$handler();
    }
}
