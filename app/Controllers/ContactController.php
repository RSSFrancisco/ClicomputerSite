<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\ContactRequest;
use App\Models\Site;

final class ContactController
{
    public function __construct(private Site $site, private PageController $pages) {}

    public function prepare(array $input): Response
    {
        [$values, $errors] = ContactRequest::validate($input);
        $url = null;
        if (!$errors) {
            $phone = ltrim($this->site->settings()['telephone'], '+');
            $url = 'https://wa.me/' . $phone . '?text=' . rawurlencode(ContactRequest::message($values));
        }
        $page = $this->site->find('/');
        $page['robots'] = 'noindex, follow';
        $page['sections'] = ['contact'];
        $page['label'] = 'Prepara tu solicitud';
        $response = $this->pages->render($page, ['values' => $values, 'errors' => $errors, 'draftUrl' => $url, 'standaloneContact' => true], $errors ? 422 : 200);
        $response->headers['Cache-Control'] = 'no-store';
        $response->headers['X-Robots-Tag'] = 'noindex, follow';
        return $response;
    }
}
