<?php
declare(strict_types=1);

namespace App\Core;

final class AdminView
{
    public const HEADERS = ['Content-Type' => 'text/html; charset=UTF-8', 'Cache-Control' => 'no-store',
        'X-Robots-Tag' => 'noindex, nofollow', 'Referrer-Policy' => 'no-referrer', 'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff', 'Content-Security-Policy' => "default-src 'self'; img-src 'self' data:; style-src 'self'; font-src 'self'; script-src 'none'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'"];

    public function __construct(private ?array $user = null, private string $token = '') {}

    public function render(string $screen, string $title, array $data = [], int $status = 200): Response
    {
        $view = new View();
        $data += ['adminUser' => $this->user, 'editorToken' => $this->token, 'adminTitle' => $title,
            'adminSection' => 'inicio', 'editorMessage' => '', 'editorErrors' => []];
        $content = $view->render('pages/' . $screen, $data);
        return new Response($view->render('layouts/admin', $data + ['content' => $content]), $status, self::HEADERS);
    }

    public function notice(string $message, int $status): Response
    {
        return $this->render('admin-notice', $status === 403 ? 'Acceso restringido' : 'No disponible', ['notice' => $message], $status);
    }

    public static function redirect(string $url): Response { return new Response('', 303, self::HEADERS + ['Location' => $url]); }
}
