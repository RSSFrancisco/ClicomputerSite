<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\ContactRequest;
use App\Models\Site;

/** Compatibilidad con formularios de pestañas antiguas. El contacto actual es directo. */
final class ContactController
{
    public function __construct(private Site $site, private PageController $pages) {}

    public function showLegacyRequest(array $input): Response
    {
        [$values, $errors] = ContactRequest::validate($input);
        $url = null;
        $notice = 'Ahora te atendemos directamente por WhatsApp. Abre el chat para compartirnos tu consulta.';
        if (!$errors) {
            $phone = ltrim($this->site->settings()['telephone'], '+');
            $url = 'https://wa.me/' . $phone . '?text=' . rawurlencode(ContactRequest::message($values));
            $notice = 'Conservamos tu consulta en el enlace de WhatsApp. Abre el chat, revisa el mensaje y pulsa Enviar.';
        }
        $page = $this->site->find('/contacto.html');
        $page['robots'] = 'noindex, follow';
        $response = $this->pages->render($page, [
            'whatsappUrl' => $url, 'contactNotice' => $notice,
        ], $errors ? 410 : 200);
        $response->headers += ['Cache-Control' => 'no-store', 'X-Robots-Tag' => 'noindex, follow', 'Referrer-Policy' => 'no-referrer'];
        return $response;
    }
}
