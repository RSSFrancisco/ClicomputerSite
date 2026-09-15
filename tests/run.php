<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Application;
use App\Models\ContactRequest;
use App\Models\Service;
use App\Models\Site;

$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    $checks++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function document(string $html): DOMXPath
{
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    return new DOMXPath($document);
}

$app = new Application();
$model = new Site(new Service());
$documents = $titles = $descriptions = [];
foreach ($model->pages() as $page) {
    $path = $page['file'] === 'index.html' ? '/' : '/' . $page['file'];
    $response = $app->handle('GET', $path);
    check($response->status === 200, 'Page failed: ' . $path);
    $xpath = document($response->body);
    $documents[$path] = $xpath;
    check($xpath->query('//h1')->length === 1, 'Expected one H1: ' . $path);
    $canonical = $xpath->query('//link[@rel="canonical"]');
    check($canonical->length === 1, 'Expected one canonical: ' . $path);
    check($canonical[0]->getAttribute('href') === $model->url($page), 'Incorrect canonical: ' . $path);
    check($xpath->query('//meta[@property="og:url"]')[0]->getAttribute('content') === $model->url($page), 'Open Graph URL mismatch');
    $titles[] = $xpath->evaluate('string(//title)');
    $descriptions[] = $xpath->query('//meta[@name="description"]')[0]->getAttribute('content');
    check($xpath->query('//meta[@property="og:image"]')->length === 1, 'Missing social image');
    check(!str_contains($response->body, '<template'), 'Content depends on a template at runtime');
    check(!str_contains($response->body, 'page-loader'), 'Blocking loader remains');
    check(!str_contains($response->body, '{{'), 'Unresolved template token');
    check(!str_contains($response->body, '525500000000'), 'Placeholder telephone remains');
    $json = $xpath->evaluate('string(//script[@type="application/ld+json"])');
    $graph = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    check(isset($graph['@graph']), 'Missing structured data');
    $ids = [];
    foreach ($xpath->query('//*[@id]') as $node) $ids[] = $node->getAttribute('id');
    check(count($ids) === count(array_unique($ids)), 'Duplicate HTML IDs: ' . $path);
    foreach ($xpath->query('//a[starts-with(@href,"tel:")]') as $link) {
        check($link->getAttribute('href') === 'tel:' . $model->settings()['telephone'], 'Telephone mismatch');
    }
    if (isset($page['service'])) {
        $services = array_values(array_filter($graph['@graph'], static fn ($node) => $node['@type'] === 'Service'));
        check(count($services) === 1 && $services[0]['url'] === $model->url($page), 'Service schema mismatch');
    }
}
check(count(array_unique($titles)) === count($titles), 'Duplicate titles');
check(count(array_unique($descriptions)) === count($descriptions), 'Duplicate descriptions');
// La búsqueda es una ruta pública, pero sus consultas no pertenecen al sitemap.
$documents['/buscar'] = document($app->handle('GET', '/buscar')->body);

foreach ($documents as $currentPath => $xpath) {
    foreach ($xpath->query('//*[@href or @src]') as $node) {
        $value = $node->getAttribute('href') ?: $node->getAttribute('src');
        if ($value === '' || preg_match('~^(?:mailto:|tel:|data:|https?://(?!www\.clcomputer\.com/))~', $value)) continue;
        check($value !== '#', 'Placeholder link: ' . $currentPath);
        $parts = parse_url($value);
        $target = $parts['path'] ?? $currentPath;
        if ($target === '') $target = $currentPath;
        if ($target[0] !== '/') $target = '/' . $target;
        if (isset($documents[$target])) {
            if (isset($parts['fragment']) && $parts['fragment'] !== '') {
                $ids = [];
                foreach ($documents[$target]->query('//*[@id]') as $element) $ids[] = $element->getAttribute('id');
                check(in_array(rawurldecode($parts['fragment']), $ids, true), 'Broken fragment: ' . $value);
            }
        } else {
            check(is_file(ROOT_PATH . $target), 'Missing internal resource: ' . $value . ' on ' . $currentPath);
        }
    }
}

$xmlResponse = $app->handle('GET', '/sitemap.xml');
$xml = simplexml_load_string($xmlResponse->body);
$urls = array_map('strval', $xml->xpath('//*[local-name()="loc"]'));
$expected = array_map(static fn ($page) => $model->url($page), $model->pages());
sort($urls);
sort($expected);
check($urls === $expected, 'Sitemap does not match public routes');
check(str_contains($app->handle('GET', '/robots.txt')->body, $model->settings()['url'] . '/sitemap.xml'), 'Robots sitemap missing');
check($app->handle('GET', '/no-existe.html')->status === 404, 'Unknown URLs must return 404');
check($app->handle('GET', '/app/Views/pages/home.php')->status === 404, 'Private view exposed');
check($app->handle('POST', '/')->status === 405, 'Unexpected method accepted');
foreach (['/index.html?utm_source=qa', '/index.php?utm_source=qa'] as $uri) {
    $response = $app->handle('GET', $uri);
    check($response->status === 301 && $response->headers['Location'] === '/?utm_source=qa', 'Legacy index redirect failed');
}

$valid = ['name' => ' Ana Pérez ', 'service' => 'Desarrollo web', 'email' => 'ana@example.com', 'phone' => '', 'message' => "Catálogo & soporte / redes\n¿Pueden cotizar?"];
$draft = $app->handle('POST', '/contacto/preparar', $valid);
check($draft->status === 200, 'Valid contact rejected');
check($draft->headers['Cache-Control'] === 'no-store', 'Draft response may be cached');
check($draft->headers['X-Robots-Tag'] === 'noindex, follow', 'Draft response can be indexed');
$xpath = document($draft->body);
$url = $xpath->query('//a[@id="preparedWhatsApp"]')[0]->getAttribute('href');
check(parse_url($url, PHP_URL_HOST) === 'wa.me', 'Invalid WhatsApp host');
check(parse_url($url, PHP_URL_PATH) === '/' . ltrim($model->settings()['telephone'], '+'), 'Invalid recipient');
parse_str(parse_url($url, PHP_URL_QUERY), $query);
check(str_contains($query['text'], $valid['message']), 'Draft lost accents or line breaks');
check($xpath->query('//textarea[@name="message"]')[0]->textContent === $valid['message'], 'Form values cleared');
check(!str_contains($draft->body, '¡Mensaje enviado!'), 'False delivery confirmation');

foreach ([[], ['name' => ['array']], array_merge($valid, ['email' => 'bad-email']), array_merge($valid, ['service' => 'Unknown']), array_merge($valid, ['message' => str_repeat('á', 1001)]), array_merge($valid, ['name' => "\xFF"])] as $input) {
    $response = $app->handle('POST', '/contacto/preparar', $input);
    check($response->status === 422, 'Invalid input accepted');
    check(document($response->body)->query('//a[@id="preparedWhatsApp" and @href]')->length === 0, 'Invalid draft has a link');
}
$attack = array_merge($valid, ['message' => '</textarea><script>alert(1)</script>']);
$response = $app->handle('POST', '/contacto/preparar', $attack);
check(!str_contains($response->body, '<script>alert(1)</script>'), 'Reflected HTML injection');
check(str_contains($response->body, '&lt;/textarea&gt;'), 'Message was not escaped');

echo "OK: $checks comprobaciones de PHP MVC, SEO, enlaces, validación y escape de datos.\n";
