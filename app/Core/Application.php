<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\ContactController;
use App\Controllers\BlogController;
use App\Controllers\AdminController;
use App\Controllers\PageController;
use App\Controllers\SearchController;
use App\Models\Search;
use App\Models\Service;
use App\Models\Site;

final class Application
{
    private Site $site;
    private PageController $pages;

    public function __construct()
    {
        $this->site = new Site(new Service());
        $this->pages = new PageController($this->site, new View());
    }

    public function handle(string $method, string $uri, array $input = []): Response
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        parse_str(parse_url($uri, PHP_URL_QUERY) ?? '', $params);
        if ($path === '/admin' || str_starts_with($path, '/admin/')) {
            return (new AdminController($this->site->blog(), $this->site, $this->pages))->handle($method, $path, $params, $input, $_FILES);
        }
        if (in_array($path, ['/index.html', '/index.php'], true) && in_array($method, ['GET', 'HEAD'], true)) {
            $query = parse_url($uri, PHP_URL_QUERY);
            return new Response('', 301, ['Location' => '/' . ($query !== null ? '?' . $query : '')]);
        }
        if (in_array($path, ['/contacto/preparar', '/contacto/enviar'], true)) {
            if ($method === 'POST') {
                parse_str(parse_url($uri, PHP_URL_QUERY) ?? '', $params);
                // Las pestañas con el formulario anterior conservan sus datos y nunca confirman un envío.
                if ($path === '/contacto/enviar' && ($params['format'] ?? '') === 'json') {
                    return new Response(json_encode([
                        'sent' => false,
                        'message' => 'Ahora recibimos las solicitudes por WhatsApp. Copia tu mensaje y abre la sección de contacto para ir al chat.',
                        'errors' => (object) [],
                    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 410, [
                        'Content-Type' => 'application/json; charset=UTF-8', 'Cache-Control' => 'no-store', 'X-Robots-Tag' => 'noindex, follow',
                    ]);
                }
                return (new ContactController($this->site, $this->pages))->showLegacyRequest($input);
            }
            return new Response('', 303, ['Location' => '/contacto.html#contacto']);
        }
        if (!in_array($method, ['GET', 'HEAD'], true)) {
            return new Response('Método no permitido.', 405, ['Content-Type' => 'text/plain; charset=UTF-8', 'Allow' => 'GET, HEAD']);
        }
        if ($path === '/buscar') {
            parse_str(parse_url($uri, PHP_URL_QUERY) ?? '', $params);
            return (new SearchController(new Search($this->site), $this->pages))->show($params);
        }
        $blog = new BlogController($this->site->blog(), $this->site, $this->pages);
        if ($path === '/noticias.html') return $blog->index($params);
        if (preg_match('~^/noticias/imagen/([a-f0-9]{32})$~D', $path, $match)) return $blog->image($match[1]);
        return match ($path) {
            '/sitemap.xml' => $this->pages->sitemap(),
            '/robots.txt' => $this->pages->robots(),
            default => $this->pages->show($path),
        };
    }
}
