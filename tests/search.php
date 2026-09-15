<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Application;
use App\Models\Search;
use App\Models\Service;
use App\Models\Site;

function verifySearch(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$search = new Search(new Site(new Service()));
$app = new Application();
foreach ([
    'cámaras' => '/seguridad.html', 'CAMARAS' => '/seguridad.html', "ca\u{0301}maras" => '/seguridad.html',
    'CCTV' => '/seguridad.html', 'Wi-Fi' => '/redes.html', 'wifi' => '/redes.html',
    'pc' => '/soporte-tecnico.html', 'reparación de computadora' => '/soporte-tecnico.html',
    'instalaciones industriales' => '/seguridad.html#industrialPhotos',
    'ceopi' => '/proyectos.html#proyecto-ceopi', 'facturación' => '/proyectos.html#proyecto-facturacion',
    'contacto' => '/#contacto',
] as $query => $expected) {
    $results = $search->find($query);
    verifySearch(($results[0]['url'] ?? '') === $expected, 'Destino incorrecto: ' . $query);
    $response = $app->handle('GET', '/buscar?' . http_build_query(['q' => $query, 'format' => 'json']));
    $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
    verifySearch($response->status === 200, 'La consulta falló: ' . $query);
    verifySearch($data['results'] === array_slice($results, 0, 5), 'Las sugerencias y PHP difieren');
    verifySearch($data['total'] === count($results), 'Conteo incorrecto');
    verifySearch(str_starts_with($response->headers['Content-Type'], 'application/json'), 'Tipo JSON incorrecto');
    verifySearch($response->headers['Cache-Control'] === 'no-store', 'La consulta puede almacenarse en caché');
    verifySearch($response->headers['X-Robots-Tag'] === 'noindex, follow', 'La búsqueda puede indexarse');
}
verifySearch($search->find('zzzzzzzzzz') === [], 'Se inventó un resultado');
verifySearch($search->find('software zzzzzzz') === [], 'Deben coincidir todas las palabras');

foreach ($search->catalog() as $entry) {
    $parts = parse_url($entry['url']);
    verifySearch(!isset($parts['host']) && str_starts_with($entry['url'], '/'), 'Destino externo inesperado');
    $response = $app->handle('GET', $parts['path']);
    verifySearch($response->status === 200, 'Enlace roto: ' . $entry['url']);
    if (isset($parts['fragment'])) verifySearch(str_contains($response->body, 'id="' . $parts['fragment'] . '"'), 'Sección inexistente: ' . $entry['url']);
}

$html = $app->handle('GET', '/buscar?q=CAMARAS');
verifySearch($html->status === 200 && str_contains($html->body, 'Cámaras de seguridad'), 'Los resultados dependen de JavaScript');
verifySearch(str_contains($html->body, 'name="robots" content="noindex, follow"'), 'Falta noindex HTML');
verifySearch(str_contains($html->body, 'href="https://www.clcomputer.com/buscar"'), 'Canónica incorrecta');
verifySearch(str_contains($app->handle('GET', '/')->body, 'action="/buscar" method="get"'), 'Falta formulario accesible sin JS');
verifySearch(!str_contains($app->handle('GET', '/sitemap.xml')->body, '/buscar'), 'El sitemap incluye búsquedas');
verifySearch($app->handle('HEAD', '/buscar?q=redes')->status === 200, 'HEAD no disponible');
verifySearch($app->handle('POST', '/buscar')->status === 405, 'Se aceptó un método incorrecto');
verifySearch($app->handle('GET', '/buscar')->status === 200, 'La búsqueda vacía debe mostrar ayuda');

foreach (['q[]=camara', 'q=%FF', 'q=a', 'q=' . str_repeat('a', 121)] as $invalid) {
    $response = $app->handle('GET', '/buscar?' . $invalid . '&format=json');
    $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
    verifySearch($response->status === 400 && $data['error'] && $data['results'] === [], 'Consulta inválida aceptada: ' . $invalid);
}
$attack = '<script>alert("xss")</script>';
$escaped = $app->handle('GET', '/buscar?q=' . rawurlencode($attack))->body;
verifySearch(!str_contains($escaped, $attack) && str_contains($escaped, e($attack)), 'Consulta sin escape HTML');

echo "OK: relevancia, acentos, sinónimos, enlaces, JSON/HTML, consultas inválidas, escape y SEO de búsqueda.\n";
