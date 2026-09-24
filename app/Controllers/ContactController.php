<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\ContactRequest;
use App\Models\Site;
use App\Services\ContactMailer;
use App\Services\ContactRateLimiter;

final class ContactController
{
    public function __construct(private Site $site, private PageController $pages, private ContactMailer $mailer, private ContactRateLimiter $rateLimiter) {}

    public function send(array $input, bool $json = false): Response
    {
        [$values, $errors] = ContactRequest::validate($input);
        $sent = false;
        $status = 422;
        $message = 'Revisa los campos indicados antes de enviar tu solicitud.';
        // Campo señuelo: no se entrega correo de formularios rellenados automáticamente.
        if (($input['website'] ?? '') !== '') {
            $message = 'No pudimos enviar tu solicitud. Recarga la página e inténtalo de nuevo.';
        } elseif (!$errors && !$this->rateLimiter->allow($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
            $status = 429;
            $message = 'No podemos aceptar más solicitudes en este momento. Espera 15 minutos o escríbenos al correo de contacto.';
        } elseif (!$errors) {
            $sent = $this->mailer->send($values, $this->site->settings());
            $status = $sent ? 200 : 503;
            $message = $sent
                ? 'Tu solicitud se ha enviado al servicio de correo. Te responderemos al email que indicaste.'
                : 'No pudimos enviar tu solicitud. Tus datos se conservan; inténtalo de nuevo más tarde o escríbenos al correo de contacto.';
        }
        $headers = ['Cache-Control' => 'no-store', 'X-Robots-Tag' => 'noindex, follow'];
        if ($json) {
            return new Response(json_encode([
                'sent' => $sent, 'message' => $message, 'errors' => (object) $errors,
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), $status,
                ['Content-Type' => 'application/json; charset=UTF-8'] + $headers);
        }
        $page = $this->site->find('/');
        $page['robots'] = 'noindex, follow';
        $page['sections'] = ['contact'];
        $page['label'] = 'Envía tu solicitud';
        $response = $this->pages->render($page, [
            'values' => $sent ? [] : $values, 'errors' => $errors, 'contactSent' => $sent,
            'contactStatus' => $message, 'standaloneContact' => true,
        ], $status);
        $response->headers += $headers;
        return $response;
    }
}
