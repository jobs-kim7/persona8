<?php
class View {
    public static function render(string $path, array $data = [], string $layout = 'main'): void {
        extract($data);
        $contentView = BASE_PATH . '/app/views/' . $path . '.php';
        $layoutView = BASE_PATH . '/app/views/layouts/' . $layout . '.php';
        include $layoutView;
    }
}
