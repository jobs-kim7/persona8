<?php
function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function old(string $key, $default = '') {
    return $_POST[$key] ?? $default;
}
