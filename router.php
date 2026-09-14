<?php
declare(strict_types=1);

// Router for `php -S 127.0.0.1:8780 router.php`. Apache uses .htaccess.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$file = realpath(__DIR__ . $path);
foreach (['assets', 'css', 'js'] as $directory) {
    $allowed = realpath(__DIR__ . '/' . $directory) . DIRECTORY_SEPARATOR;
    if ($file && strncmp($file, $allowed, strlen($allowed)) === 0 && is_file($file)) {
        return false;
    }
}
require __DIR__ . '/index.php';
