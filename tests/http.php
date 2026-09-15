<?php
declare(strict_types=1);

// Run after starting `php -S 127.0.0.1:8780 router.php`.
require dirname(__DIR__) . '/app/bootstrap.php';

function request(string $url, array $headers = [], ?array $post = null): array
{
    $curl = curl_init($url);
    $responseHeaders = [];
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
            if (str_contains($line, ':')) {
                [$name, $value] = explode(':', $line, 2);
                $responseHeaders[strtolower(trim($name))] = trim($value);
            }
            return strlen($line);
        },
    ]);
    if ($post !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    $body = curl_exec($curl);
    if ($body === false) throw new RuntimeException(curl_error($curl));
    return [(int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE), $responseHeaders, $body];
}

function verify(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$model = new App\Models\Site(new App\Models\Service());
foreach ($model->pages() as $page) {
    $path = $page['file'] === 'index.html' ? '/' : '/' . $page['file'];
    [$status, $headers, $body] = request('http://127.0.0.1:8780' . $path);
    verify($status === 200 && str_contains($body, '<h1'), 'HTTP page failed: ' . $path);
}
verify(request('http://127.0.0.1:8780/no-existe.html')[0] === 404, 'Expected real HTTP 404');
verify(request('http://127.0.0.1:8780/app/Views/pages/home.php')[0] === 404, 'Internal view exposed');
foreach (['/sitemap.xml' => 'application/xml', '/robots.txt' => 'text/plain'] as $path => $type) {
    [$status, $headers] = request('http://127.0.0.1:8780' . $path);
    verify($status === 200 && str_starts_with($headers['content-type'], $type), 'Wrong dynamic resource type');
}
[$status, $headers] = request('http://127.0.0.1:8780/index.html?origen=prueba');
verify($status === 301 && $headers['location'] === '/?origen=prueba', 'Legacy HTTP redirect failed');
[$status, $headers, $body] = request('http://127.0.0.1:8780/contacto/preparar', [], [
    'name' => 'Prueba local', 'service' => 'Desarrollo web', 'message' => 'Preparar borrador sin enviar.',
]);
verify($status === 200 && $headers['cache-control'] === 'no-store', 'HTTP contact failed');
verify(str_contains($body, 'https://wa.me/' . ltrim($model->settings()['telephone'], '+') . '?text='), 'PHP did not prepare the draft');
verify(request('http://127.0.0.1:8780/contacto/preparar', [], ['name' => ''])[0] === 422, 'Invalid HTTP contact accepted');
[$status, $headers, $body] = request('http://127.0.0.1:8780/buscar?q=camaras&format=json');
$suggestions = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
verify($status === 200 && str_starts_with($headers['content-type'], 'application/json'), 'HTTP search JSON failed');
verify(($suggestions['results'][0]['url'] ?? '') === '/seguridad.html', 'HTTP search lost the query');
[$status, $headers, $body] = request('http://127.0.0.1:8780/buscar?q=wifi');
verify($status === 200 && $headers['x-robots-tag'] === 'noindex, follow' && str_contains($body, 'Redes e infraestructura'), 'HTTP search HTML failed');
echo "OK: nueve páginas MVC, recursos SEO, 404, redirección, búsqueda y formulario PHP por HTTP.\n";

// Optional Apache checks use a separate loopback-only instance, never the system service.
$httpd = '/usr/sbin/httpd';
if (!is_executable($httpd) || !is_dir('/usr/libexec/apache2')) {
    echo "Apache local no disponible; comprueba .htaccess en el alojamiento.\n";
    exit(0);
}
$dir = sys_get_temp_dir() . '/clicomputer-apache-' . getmypid();
mkdir($dir, 0700);
$public = $dir . '/public';
mkdir($public);
mkdir($public . '/assets/img', 0755, true);
mkdir($public . '/app/Views/pages', 0755, true);
copy(ROOT_PATH . '/.htaccess', $public . '/.htaccess');
copy(ROOT_PATH . '/assets/img/favicon.svg', $public . '/assets/img/favicon.svg');
// A marker tests Apache routing independently of the PHP handler tested above.
file_put_contents($public . '/index.php', 'MVC route marker');
file_put_contents($public . '/app/Views/pages/home.php', 'Private view marker');
$config = "ServerRoot \"$dir\"\nServerName localhost\nPidFile \"$dir/httpd.pid\"\nErrorLog \"$dir/error.log\"\nListen 127.0.0.1:8781\n";
foreach (['mpm_event','unixd','authz_core','authz_host','mime','dir','rewrite','filter','deflate','expires'] as $module) {
    $config .= "LoadModule {$module}_module /usr/libexec/apache2/mod_{$module}.so\n";
}
$config .= 'TypesConfig /etc/apache2/mime.types' . "\nDocumentRoot \"$public\"\n<Directory \"$public\">\nAllowOverride All\nRequire all granted\n</Directory>\n";
file_put_contents($dir . '/httpd.conf', $config);
$process = proc_open([$httpd, '-f', $dir . '/httpd.conf', '-DFOREGROUND'],
    [0 => ['pipe','r'], 1 => ['file','/dev/null','a'], 2 => ['pipe','w']], $pipes);
try {
    $ready = false;
    for ($attempt = 0; $attempt < 30; $attempt++) {
        $socket = @fsockopen('127.0.0.1', 8781, $errno, $error, .1);
        if ($socket) { fclose($socket); $ready = true; break; }
        usleep(100000);
    }
    verify($ready, 'Apache failed to start; inspect ' . $dir . '/error.log');
    foreach (['www.clcomputer.com', 'clcomputer.com'] as $host) {
        [$status, $headers] = request('http://127.0.0.1:8781/redes.html?origen=prueba', ['Host: ' . $host]);
        verify($status === 301 && ($headers['location'] ?? '') === 'https://www.clcomputer.com/redes.html?origen=prueba', 'Canonical Apache redirect failed: ' . $status . ' ' . json_encode($headers));
    }
    foreach (['index.html', 'index.php'] as $file) {
        [$status, $headers] = request('http://127.0.0.1:8781/' . $file . '?origen=prueba', ['Host: www.clcomputer.com']);
        verify($status === 301 && $headers['location'] === 'https://www.clcomputer.com/?origen=prueba', 'Apache index redirect failed');
    }
    verify(request('http://127.0.0.1:8781/app/Views/pages/home.php', ['Host: localhost'])[0] === 403, 'Apache exposed internal views');
    verify(request('http://127.0.0.1:8781/assets/img/favicon.svg', ['Host: localhost'])[0] === 200, 'Apache blocked a public asset');
    verify(request('http://127.0.0.1:8781/redes.html', ['Host: localhost'])[2] === 'MVC route marker', 'Apache did not route to MVC');
    verify(request('http://127.0.0.1:8781/buscar?q=camaras', ['Host: localhost'])[2] === 'MVC route marker', 'Apache did not route search to MVC');
    echo "OK: reglas Apache de redirección, rutas y parámetros conservados, protección de vistas y recursos públicos.\n";
} finally {
    proc_terminate($process);
    foreach ($pipes as $pipe) fclose($pipe);
    proc_close($process);
}
