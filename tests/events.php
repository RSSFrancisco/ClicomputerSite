<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Application;
use App\Models\Search;
use App\Models\Service;
use App\Models\Site;

$app = new Application();
$checks = 0;
function verifyEvent(bool $condition, string $message): void
{
    global $checks;
    $checks++;
    if (!$condition) throw new RuntimeException($message);
}
function eventDocument(string $html): DOMXPath
{
    $dom = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    return new DOMXPath($dom);
}

$base = '/eventos/anselmo-y-dulce';
foreach (['/eventos', $base] as $path) {
    foreach (['GET', 'HEAD'] as $method) {
        $response = $app->handle($method, $path . '?origen=invitacion');
        verifyEvent($response->status === 200, 'Invitation route failed: ' . $path);
        verifyEvent($response->headers['X-Robots-Tag'] === 'noindex, nofollow', 'Missing HTTP robots protection');
        verifyEvent($response->headers['Referrer-Policy'] === 'no-referrer', 'Invitation leaks referring URL');
        $document = eventDocument($response->body);
        verifyEvent($document->query('//meta[@name="robots" and @content="noindex, nofollow"]')->length === 1, 'Missing robots metadata');
        verifyEvent($document->query('//h1')->length === 1, 'Expected one heading');
        verifyEvent($document->evaluate('string(//link[@rel="canonical"]/@href)') === 'https://www.clcomputer.com' . $path, 'Canonical includes tracking parameters');
        verifyEvent(!str_contains($response->body, 'googletagmanager') && !str_contains($response->body, 'application/ld+json'), 'Invitation includes public analytics or SEO schema');
    }
}
$landing = $app->handle('GET', $base);
$document = eventDocument($landing->body);
verifyEvent($document->query('//time[@datetime="2026-11-28"]')->length === 1, 'Wedding date changed');
verifyEvent($document->query('//*[@data-countdown="2026-11-28T16:00:00-06:00"]')->length === 1, 'Countdown has an ambiguous timezone');
verifyEvent($document->query('//time[@datetime="2026-11-28T16:00:00-06:00"]')->length === 1, 'Ceremony time changed');
verifyEvent($document->query('//time[@datetime="2026-11-28T17:30:00-06:00"]')->length === 1, 'Reception time changed');
foreach ($document->query('//a[contains(@href,"google.com/maps/")]') as $link) {
    verifyEvent(str_contains($link->getAttribute('rel'), 'nofollow') && str_contains($link->getAttribute('rel'), 'noreferrer'), 'External map leaks referrer or allows following');
    parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $params);
    verifyEvent(($params['api'] ?? '') === '1' && !empty($params['query']), 'Maps search is incomplete');
}
verifyEvent($document->query('//dialog[@data-invitation-dialog]')->length === 1, 'Original invitation cannot be opened');
verifyEvent($document->query('//audio[@data-wedding-audio and @autoplay and @loop and starts-with(@src,"/eventos/anselmo-y-dulce/musica.mp3?v=")]')->length === 1, 'Local autoplaying and looping song is missing');
verifyEvent($document->query('//script[contains(@src,"/js/components/wedding-music.js")]')->length === 1, 'Wedding music controller is missing');
verifyEvent(!str_contains(strtolower($landing->body), 'youtube') && $document->query('//iframe')->length === 0, 'YouTube or its unavailable notice remains');
verifyEvent(!str_contains($app->handle('GET', '/eventos')->body, 'wedding-music.js'), 'Music is loaded outside the invitation');
foreach (['/eventos.html' => '/eventos', '/eventos/' => '/eventos', $base . '/' => $base] as $source => $target) {
    $redirect = $app->handle('GET', $source);
    verifyEvent($redirect->status === 301 && $redirect->headers['Location'] === $target, 'Event URL alias failed');
    verifyEvent($redirect->headers['X-Robots-Tag'] === 'noindex, nofollow', 'Redirect is indexable');
}
verifyEvent($app->handle('GET', '/eventos/no-existe')->status === 404, 'Unknown event does not return 404');
verifyEvent($app->handle('POST', $base)->status === 405, 'Unexpected event mutation accepted');

$calendar = $app->handle('GET', $base . '/calendario.ics');
verifyEvent($calendar->status === 200 && $calendar->headers['Content-Type'] === 'text/calendar; charset=UTF-8', 'Calendar is not downloadable');
verifyEvent($calendar->headers['X-Robots-Tag'] === 'noindex, nofollow', 'Calendar is indexable');
verifyEvent(str_contains($calendar->headers['Content-Disposition'], 'attachment;'), 'Calendar does not download');
$unfolded = str_replace("\r\n ", '', $calendar->body);
verifyEvent(substr_count($unfolded, 'BEGIN:VEVENT') === 2, 'Calendar must contain both moments');
verifyEvent(str_contains($unfolded, 'DTSTART:20261128T220000Z') && str_contains($unfolded, 'DTSTART:20261128T233000Z'), 'Calendar timezone conversion failed');
verifyEvent(str_contains($unfolded, 'Parroquia San Rafael Guizar y Valencia') && str_contains($unfolded, 'Salón el Campesino'), 'Calendar lost a venue');
foreach (explode("\r\n", $calendar->body) as $line) {
    verifyEvent(strlen($line) <= 75 && preg_match('//u', $line) === 1, 'Calendar line is too long or corrupts UTF-8');
}
$image = $app->handle('GET', $base . '/invitacion.png');
verifyEvent($image->status === 200 && $image->headers['Content-Type'] === 'image/png', 'Original invitation failed');
verifyEvent($image->headers['X-Robots-Tag'] === 'noindex, nofollow', 'Original image is indexable');
verifyEvent(hash('sha256', $image->body) === hash_file('sha256', ROOT_PATH . '/data/events/anselmo-y-dulce/invitacion.png'), 'Original attachment was changed');
verifyEvent($app->handle('GET', '/data/events/anselmo-y-dulce/invitacion.png')->status === 404, 'Direct event data exposed');
$audio = $app->handle('GET', $base . '/musica.mp3');
$audioFile = ROOT_PATH . '/data/events/anselmo-y-dulce/symphony.mp3';
$audioSize = filesize($audioFile);
verifyEvent($audio->status === 200 && $audio->headers['Content-Type'] === 'audio/mpeg', 'Local music cannot be played');
verifyEvent($audio->headers['X-Robots-Tag'] === 'noindex, nofollow' && $audio->headers['Accept-Ranges'] === 'bytes', 'Music headers are incomplete');
verifyEvent(hash('sha256', $audio->body) === hash_file('sha256', $audioFile), 'Supplied MP3 was changed');
verifyEvent((int) $audio->headers['Content-Length'] === $audioSize, 'Music length is incorrect');
$previousRange = $_SERVER['HTTP_RANGE'] ?? null;
$previousIfRange = $_SERVER['HTTP_IF_RANGE'] ?? null;
try {
    unset($_SERVER['HTTP_IF_RANGE']);
    foreach (['bytes=0-1' => [0, 2], 'bytes=300-399' => [300, 100], 'bytes=-16' => [$audioSize - 16, 16], 'bytes=' . ($audioSize - 10) . '-' => [$audioSize - 10, 10]] as $range => [$offset, $length]) {
        $_SERVER['HTTP_RANGE'] = $range;
        $partial = $app->handle('GET', $base . '/musica.mp3');
        verifyEvent($partial->status === 206, 'Audio range was not served: ' . $range);
        verifyEvent($partial->body === substr($audio->body, $offset, $length), 'Audio range bytes are wrong');
        verifyEvent($partial->headers['Content-Range'] === 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $audioSize, 'Audio range header is wrong');
        verifyEvent((int) $partial->headers['Content-Length'] === $length, 'Partial audio length is wrong');
        verifyEvent($partial->headers['X-Robots-Tag'] === 'noindex, nofollow', 'Partial music is indexable');
    }
    foreach (['bytes=' . $audioSize . '-', 'bytes=30-20', 'bytes=-0'] as $range) {
        $_SERVER['HTTP_RANGE'] = $range;
        $invalid = $app->handle('GET', $base . '/musica.mp3');
        verifyEvent($invalid->status === 416 && $invalid->headers['Content-Range'] === 'bytes */' . $audioSize && $invalid->body === '', 'Unsatisfiable range accepted');
    }
    $_SERVER['HTTP_RANGE'] = 'bytes=0-1';
    $_SERVER['HTTP_IF_RANGE'] = '"unknown-version"';
    verifyEvent($app->handle('GET', $base . '/musica.mp3')->status === 200, 'An unknown If-Range validator must receive the complete file');
} finally {
    if ($previousRange === null) unset($_SERVER['HTTP_RANGE']); else $_SERVER['HTTP_RANGE'] = $previousRange;
    if ($previousIfRange === null) unset($_SERVER['HTTP_IF_RANGE']); else $_SERVER['HTTP_IF_RANGE'] = $previousIfRange;
}
verifyEvent($app->handle('GET', '/data/events/anselmo-y-dulce/symphony.mp3')->status === 404, 'Direct MP3 data exposed');
verifyEvent(!str_contains($app->handle('GET', '/sitemap.xml')->body, '/eventos'), 'Events leaked into the sitemap');
// No se bloquea robots.txt: los buscadores deben poder leer la instrucción noindex.
verifyEvent(!str_contains($app->handle('GET', '/robots.txt')->body, 'Disallow: /eventos'), 'Crawler cannot read noindex');
$site = new Site(new Service());
$search = new Search($site);
verifyEvent($search->find('Anselmo') === [] && $search->find('Dulce') === [], 'Invitation leaked into internal search');
foreach ($site->pages() as $page) verifyEvent(!str_starts_with($page['file'], 'eventos'), 'Private invitation added to public catalog');
foreach (['/', '/contacto.html'] as $path) {
    verifyEvent(eventDocument($app->handle('GET', $path)->body)->query('//a[@href="/eventos" and @rel="nofollow"]')->length >= 1, 'Event section missing from navigation');
}
echo "OK: $checks comprobaciones de invitaciones, noindex/nofollow, catálogo, calendario, imagen y rutas.\n";
