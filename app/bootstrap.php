<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return;
    }
    $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function asset(string $path): string
{
    static $versions = [];
    $path = ltrim($path, '/');
    $versions[$path] ??= substr(hash_file('sha256', ROOT_PATH . '/' . $path), 0, 12);
    return '/' . $path . '?v=' . $versions[$path];
}
