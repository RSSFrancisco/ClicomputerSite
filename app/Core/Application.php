<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\ContactController;
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
        if (in_array($path, ['/index.html', '/index.php'], true) && in_array($method, ['GET', 'HEAD'], true)) {
            $query = parse_url($uri, PHP_URL_QUERY);
            return new Response('', 301, ['Location' => '/' . ($query !== null ? '?' . $query : '')]);
        }
        if ($path === '/contacto/preparar') {
            if ($method === 'POST') {
                return (new ContactController($this->site, $this->pages))->prepare($input);
            }
            return new Response('', 303, ['Location' => '/#contacto']);
        }
        if (!in_array($method, ['GET', 'HEAD'], true)) {
            return new Response('Método no permitido.', 405, ['Content-Type' => 'text/plain; charset=UTF-8', 'Allow' => 'GET, HEAD']);
        }
        if ($path === '/buscar') {
            parse_str(parse_url($uri, PHP_URL_QUERY) ?? '', $params);
            return (new SearchController(new Search($this->site), $this->pages))->show($params);
        }
        return match ($path) {
            '/sitemap.xml' => $this->pages->sitemap(),
            '/robots.txt' => $this->pages->robots(),
            default => $this->pages->show($path),
        };
    }
}
