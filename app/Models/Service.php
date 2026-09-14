<?php
declare(strict_types=1);

namespace App\Models;

final class Service
{
    private array $services;

    public function __construct()
    {
        $this->services = require ROOT_PATH . '/data/services.php';
    }

    public function all(): array
    {
        return $this->services;
    }
}
