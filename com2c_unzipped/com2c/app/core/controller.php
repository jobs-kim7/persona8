<?php
abstract class Controller {
    protected function view(string $path, array $data = [], string $layout = 'main'): void {
        View::render($path, $data, $layout);
    }

    protected function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }
}
