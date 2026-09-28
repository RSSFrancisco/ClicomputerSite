<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Blog;
use App\Models\Site;
use Throwable;

final class BlogController
{
    public function __construct(private Blog $blog, private Site $site, private PageController $pages) {}

    public function index(array $params): Response
    {
        $category = $params['categoria'] ?? '';
        $number = $params['pagina'] ?? '1';
        if (!is_string($category) || ($category !== '' && !isset(Blog::CATEGORIES[$category]))
            || !is_string($number) || !preg_match('/^[1-9][0-9]{0,5}$/D', $number)) return $this->pages->notFound();
        $posts = array_values(array_filter($this->blog->publicPosts(), static fn ($post) => $category === '' || $post['category'] === $category));
        $count = max(1, (int) ceil(count($posts) / 9));
        if ((int) $number > $count) return $this->pages->notFound();
        $page = $this->site->find('/noticias.html');
        if ($category !== '' || $number !== '1') $page['robots'] = 'noindex, follow';
        $response = $this->pages->render($page, [
            'newsPosts' => array_slice($posts, ((int) $number - 1) * 9, 9),
            'newsCategory' => $category, 'newsPage' => (int) $number, 'newsPageCount' => $count,
        ], $this->blog->unavailable() ? 503 : 200);
        if ($this->blog->unavailable()) $response->headers['Retry-After'] = '300';
        return $response;
    }

    public function image(string $id, bool $private = false): Response
    {
        try { $image = $this->blog->image($id, $private); } catch (Throwable) { $image = null; }
        if (!$image) return new Response('', 404, ['Cache-Control' => 'no-store']);
        return new Response($image['content'], 200, [
            'Content-Type' => $image['mime'], 'Content-Length' => (string) strlen($image['content']),
            'X-Content-Type-Options' => 'nosniff', 'Content-Security-Policy' => "default-src 'none'; sandbox",
            // No conservar fotos después de retirar una noticia.
            'Cache-Control' => 'no-store', 'X-Robots-Tag' => $private ? 'noindex, nofollow' : 'noindex',
        ]);
    }
}
