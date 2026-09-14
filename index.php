<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

try {
    $response = (new App\Core\Application())->handle(
        $_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/', $_POST
    );
} catch (Throwable $error) {
    error_log('Clicomputer: ' . $error->getMessage());
    $response = new App\Core\Response('No pudimos cargar esta página. Intenta nuevamente en unos momentos.', 500);
}
$response->send(($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD');
