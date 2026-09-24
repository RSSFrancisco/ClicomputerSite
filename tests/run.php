<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Application;
use App\Models\ContactRequest;
use App\Models\Service;
use App\Models\Site;
use App\Services\ContactMailer;
use App\Services\ContactRateLimiter;

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

$mailCalls = [];
$mailAccepted = true;
$testSmtp = ['username' => 'info@clcomputer.com', 'password' => 'test-password-not-real', 'port' => 587];
ini_set('error_log', sys_get_temp_dir() . '/clicomputer-test-mail-' . getmypid() . '.log');
$mailer = new ContactMailer(static function ($mail) use (&$mailCalls, &$mailAccepted): bool {
    $mail->preSend();
    $to = $mail->getToAddresses()[0][0];
    $subject = $mail->Subject;
    $body = base64_encode($mail->Body);
    $headers = ['From' => $mail->FromName . ' <' . $mail->From . '>', 'Reply-To' => array_values($mail->getReplyToAddresses())[0][0]];
    $mailCalls[] = compact('to', 'subject', 'body', 'headers');
    return $mailAccepted;
}, $testSmtp);
$rateLimiter = new ContactRateLimiter(sys_get_temp_dir() . '/clicomputer-test-' . bin2hex(random_bytes(8)), 100);
$app = new Application($mailer, $rateLimiter);
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
$sent = $app->handle('POST', '/contacto/enviar', $valid);
check($sent->status === 200, 'Valid contact rejected');
check($sent->headers['Cache-Control'] === 'no-store', 'Contact response may be cached');
check($sent->headers['X-Robots-Tag'] === 'noindex, follow', 'Contact response can be indexed');
$xpath = document($sent->body);
check(count($mailCalls) === 1, 'Expected one delivery');
check($mailCalls[0]['to'] === $model->settings()['email'], 'Incorrect mail recipient');
check($mailCalls[0]['headers']['Reply-To'] === $valid['email'], 'Missing visitor reply address');
check($mailCalls[0]['headers']['From'] === 'Clicomputer <' . $testSmtp['username'] . '>', 'Sender must be the authenticated mailbox');
$mailText = str_replace("\r\n", "\n", base64_decode($mailCalls[0]['body'], true));
check(str_contains($mailText, $valid['message']) && str_contains($mailText, 'Nombre: Ana Pérez'), 'Mail lost accents, values or line breaks');
check($xpath->query('//textarea[@name="message"]')[0]->textContent === '', 'Successful form not cleared');
check(str_contains($xpath->evaluate('string(//*[@id="formAlerts"])'), 'enviado al servicio de correo'), 'Mail acceptance not confirmed');
check($xpath->query('//form[@id="contactForm"]')[0]->getAttribute('action') === '/contacto/enviar#contacto', 'Form targets wrong endpoint');
check($xpath->query('//input[@name="email" and @required]')->length === 1, 'Reply address must be required');
check(!str_contains($sent->body, 'preparedWhatsApp'), 'WhatsApp draft remains');

foreach ([[], ['name' => ['array']], array_merge($valid, ['email' => '']), array_merge($valid, ['email' => 'bad-email']), array_merge($valid, ['email' => "ana@example.com\r\nBcc: another@example.com"]), array_merge($valid, ['service' => 'Unknown']), array_merge($valid, ['message' => str_repeat('á', 1001)]), array_merge($valid, ['name' => "\xFF"]), array_merge($valid, ['website' => 'spam']), array_merge($valid, ['website' => ['array']])] as $input) {
    $response = $app->handle('POST', '/contacto/enviar', $input);
    check($response->status === 422, 'Invalid input accepted');
    check(count($mailCalls) === 1, 'Invalid input reached the mail service');
}
$mailAccepted = false;
$attack = array_merge($valid, ['message' => '</textarea><script>alert(1)</script>']);
$response = $app->handle('POST', '/contacto/enviar', $attack);
check($response->status === 503, 'Mail failure reported as success');
check(!str_contains($response->body, '<script>alert(1)</script>'), 'Reflected HTML injection');
check(str_contains($response->body, '&lt;/textarea&gt;'), 'Message was not escaped');
check(document($response->body)->query('//textarea[@name="message"]')[0]->textContent === $attack['message'], 'Failure discarded the message');
$failed = $app->handle('POST', '/contacto/enviar?format=json', $valid);
check($failed->status === 503 && json_decode($failed->body, true)['sent'] === false, 'JSON mail failure reported as success');
$mailAccepted = true;
$jsonResponse = $app->handle('POST', '/contacto/enviar?format=json', $valid);
$payload = json_decode($jsonResponse->body, true, 512, JSON_THROW_ON_ERROR);
check($jsonResponse->status === 200 && $payload['sent'] === true, 'JSON success missing');
check(!str_contains($jsonResponse->body, $valid['email']), 'JSON unnecessarily echoes personal data');
$invalid = $app->handle('POST', '/contacto/enviar?format=json', ['email' => 'bad']);
check($invalid->status === 422 && isset(json_decode($invalid->body, true)['errors']['email']), 'JSON field errors missing');
$before = count($mailCalls);
foreach (['GET', 'HEAD', 'POST'] as $method) {
    check($app->handle($method, '/contacto/preparar', $valid)->status === 303, 'Old form route must show new flow');
}
check($app->handle('GET', '/contacto/enviar')->status === 303, 'GET can send mail');
check(count($mailCalls) === $before, 'A legacy or GET request sent mail');
$limitedApp = new Application($mailer, new ContactRateLimiter(sys_get_temp_dir() . '/clicomputer-limit-test-' . bin2hex(random_bytes(8)), 1));
check($limitedApp->handle('POST', '/contacto/enviar', $valid)->status === 200, 'First submission blocked');
check($limitedApp->handle('POST', '/contacto/enviar?format=json', $valid)->status === 429, 'Repeat submissions not limited');
check(count($mailCalls) === $before + 1, 'Rate-limited submission reached mail service');
foreach (['email', 'mail_from'] as $key) {
    $settings = array_merge($model->settings(), [$key => "bad@example.com\r\nBcc: another@example.com"]);
    check(!$mailer->send($valid, $settings), 'Configured header injection accepted');
}

echo "OK: $checks comprobaciones de PHP MVC, SEO, enlaces, validación y escape de datos.\n";
