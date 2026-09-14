<?php
declare(strict_types=1);

namespace App\Models;

final class Site
{
    private array $config;
    private array $pages;

    public function __construct(Service $services)
    {
        $this->config = require ROOT_PATH . '/config/site.php';
        $this->pages = array_merge($this->config['pages'], $services->all(), [[
            'file' => 'cv.html', 'label' => 'Perfil profesional',
            'title' => 'CV - Francisco Reyes Sánchez',
            'description' => 'Perfil profesional de Francisco Reyes Sánchez: experiencia en desarrollo de software, páginas web e infraestructura tecnológica. Consulta sus proyectos y habilidades.',
            'sections' => ['cv'],
        ]]);
    }

    public function settings(): array
    {
        return $this->config;
    }

    public function pages(): array
    {
        return $this->pages;
    }

    public function find(string $path): ?array
    {
        $file = $path === '/' ? 'index.html' : ltrim($path, '/');
        foreach ($this->pages as $page) {
            if ($page['file'] === $file) {
                return $page;
            }
        }
        return null;
    }

    public function url(array $page): string
    {
        return rtrim($this->config['url'], '/') . '/' . ($page['file'] === 'index.html' ? '' : $page['file']);
    }
}
