<?php
declare(strict_types=1);

namespace App\Models;

final class Site
{
    private array $config;
    private array $pages;
    private Blog $blog;

    public function __construct(Service $services, ?Blog $blog = null)
    {
        $this->blog = $blog ?? new Blog();
        $this->config = require ROOT_PATH . '/config/site.php';
        $this->pages = array_merge($this->config['pages'], $services->all(), require ROOT_PATH . '/data/editorial.php', [[
            'file' => 'cv.html', 'label' => 'Perfil profesional',
            'title' => 'CV - Francisco Reyes Sánchez',
            'description' => 'Perfil profesional de Francisco Reyes Sánchez: experiencia en desarrollo de software, páginas web e infraestructura tecnológica. Consulta sus proyectos y habilidades.',
            'sections' => ['cv'],
        ]]);
        $this->pages[] = [
            'file' => 'noticias.html', 'label' => 'Noticias', 'type' => 'CollectionPage',
            'title' => 'Noticias de tecnología y Veracruz | Clicomputer',
            'description' => 'Noticias de tecnología, novedades digitales y actualidad de Veracruz en el blog de Clicomputer.',
            'sections' => ['news'], 'icon' => 'bi-newspaper',
        ];
        $this->pages = array_merge($this->pages, array_map([Blog::class, 'page'], $this->blog->publicPosts()));
        $scopes = require ROOT_PATH . '/data/service-scopes.php';
        foreach ($this->pages as &$page) {
            if (isset($scopes[$page['file']])) $page['scope'] = $scopes[$page['file']];
        }
        unset($page);
    }

    public function settings(): array
    {
        return $this->config;
    }

    public function blog(): Blog { return $this->blog; }

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
