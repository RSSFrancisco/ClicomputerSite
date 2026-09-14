<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Site;

final class Seo
{
    public function __construct(private Site $model) {}

    public function graph(array $page): array
    {
        $site = $this->model->settings();
        $base = rtrim($site['url'], '/');
        $url = $this->model->url($page);
        $graph = [[
            '@type' => 'Organization', '@id' => $base . '/#organization',
            'name' => $site['name'], 'url' => $base . '/',
            'logo' => $base . '/assets/img/clicomputer-symbol.png',
            'telephone' => $site['telephone'], 'email' => $site['email'],
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => $site['locality'],
                'addressRegion' => $site['region'], 'addressCountry' => $site['country']],
            'contactPoint' => ['@type' => 'ContactPoint', 'telephone' => $site['telephone'],
                'contactType' => 'customer service', 'availableLanguage' => ['es']],
        ], [
            '@type' => 'WebSite', '@id' => $base . '/#website', 'url' => $base . '/',
            'name' => $site['name'], 'inLanguage' => 'es-MX',
            'publisher' => ['@id' => $base . '/#organization'],
        ], [
            '@type' => $page['file'] === 'proyectos.html' ? 'CollectionPage' : 'WebPage',
            '@id' => $url . '#webpage', 'url' => $url, 'name' => $page['title'],
            'description' => $page['description'], 'inLanguage' => 'es-MX',
            'isPartOf' => ['@id' => $base . '/#website'],
        ]];
        if (!in_array($page['file'], ['index.html', '404.html'], true)) {
            $graph[2]['breadcrumb'] = ['@id' => $url . '#breadcrumb'];
            $graph[] = ['@type' => 'BreadcrumbList', '@id' => $url . '#breadcrumb',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => $base . '/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $page['label'], 'item' => $url],
                ]];
        }
        if (isset($page['service'])) {
            $graph[2]['mainEntity'] = ['@id' => $url . '#service'];
            $graph[] = ['@type' => 'Service', '@id' => $url . '#service', 'url' => $url,
                'name' => $page['service'], 'description' => $page['description'],
                'provider' => ['@id' => $base . '/#organization'],
                'areaServed' => ['@type' => 'City', 'name' => $site['locality'] . ', ' . $site['region']]];
        }
        return ['@context' => 'https://schema.org', '@graph' => $graph];
    }
}
