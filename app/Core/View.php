<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public function render(string $name, array $data = []): string
    {
        if (!preg_match('~^[a-zA-Z0-9/_-]+$~', $name)) {
            throw new \InvalidArgumentException('Invalid view name');
        }
        $file = ROOT_PATH . '/app/Views/' . $name . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException('View not found: ' . $name);
        }
        $view = $this;
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require $file;
            return (string) ob_get_clean();
        } catch (\Throwable $error) {
            ob_end_clean();
            throw $error;
        }
    }
}
