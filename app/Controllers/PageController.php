<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Seo;
use App\Core\View;
use App\Models\Site;

final class PageController
{
    public function __construct(private Site $model, private View $view) {}

    public function show(string $path): Response
    {
        $page = $this->model->find($path);
        return $page ? $this->render($page) : $this->notFound();
    }

    public function render(array $page, array $extra = [], int $status = 200): Response
    {
        $data = array_merge([
            'site' => $this->model->settings(), 'page' => $page,
            'canonical' => $this->model->url($page), 'schema' => (new Seo($this->model))->graph($page),
            'pages' => $this->model->pages(), 'values' => [], 'errors' => [], 'contactSent' => false, 'contactStatus' => '',
        ], $extra);
        $content = '';
        foreach ($page['sections'] ?? ['service'] as $section) {
            $content .= '<div id="section-' . e($section) . '">' . $this->view->render('pages/' . $section, $data) . '</div>';
        }
        $layout = $page['file'] === 'cv.html' ? 'cv' : 'master';
        return new Response($this->view->render('layouts/' . $layout, $data + ['content' => $content]), $status);
    }

    public function notFound(int $status = 404): Response
    {
        return $this->render([
            'file' => '404.html', 'title' => 'Página no encontrada | Clicomputer',
            'description' => 'Consulta los servicios de Clicomputer o regresa a la página de inicio.',
            'label' => 'Página no encontrada', 'robots' => 'noindex, follow', 'sections' => ['not-found'],
        ], [], $status);
    }

    public function sitemap(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($this->model->pages() as $page) {
            $xml .= '  <url><loc>' . e($this->model->url($page)) . '</loc></url>' . "\n";
        }
        return new Response($xml . '</urlset>' . "\n", 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $text = "User-agent: *\nAllow: /\n\nSitemap: " . $this->model->settings()['url'] . "/sitemap.xml\n";
        return new Response($text, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
