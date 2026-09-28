<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
// Alternativa de CLI para probar una configuración privada explícita.
if (($argv[1] ?? '') === '--config' && is_string($argv[2] ?? null)) {
    putenv('CLICOMPUTER_DB_CONFIG=' . $argv[2]);
    putenv('CLICOMPUTER_BLOG_TEST=1');
}

use App\Core\Application;
use App\Models\Blog;
use App\Models\AdminUser;
use App\Services\AdminSchema;
use App\Models\Search;
use App\Models\Service;
use App\Models\Site;
use App\Services\BlogDatabase;

if (getenv('CLICOMPUTER_BLOG_TEST') !== '1') {
    fwrite(STDERR, "Usa CLICOMPUTER_BLOG_TEST=1 y una base desechable terminada en _test.\n");
    exit(1);
}
$db = (new BlogDatabase())->connection();
if (!str_ends_with((string) $db->query('SELECT DATABASE()')->fetchColumn(), '_test')) throw new RuntimeException('Se requiere una base desechable terminada en _test.');
$existingTables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach (['users', 'blog_posts', 'blog_images', 'blog_login_attempts', 'clicomputer_blog_users', 'clicomputer_blog_posts', 'clicomputer_blog_images', 'clicomputer_blog_login_attempts'] as $table) {
    if (in_array($table, $existingTables, true) && (int) $db->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn() !== 0) throw new RuntimeException('La base de prueba debe estar vacía.');
}
AdminSchema::migrate($db);
$checks = 0;
function verifyBlog(bool $condition, string $message): void { global $checks; $checks++; if (!$condition) throw new RuntimeException($message); }
function rejectsBlog(callable $action, string $message): void { try { $action(); } catch (RuntimeException) { verifyBlog(true, $message); return; } verifyBlog(false, $message); }
$dir = sys_get_temp_dir() . '/clicomputer-blog-qa-' . bin2hex(random_bytes(6));
mkdir($dir, 0700);
$process = null;
$log = $dir . '/http.log';
$cookie = $dir . '/cookies.txt';
function blogHttp(string $path, ?array $post = null, bool $cookies = true): array {
    global $cookie;
    $headers = [];
    $curl = curl_init('http://127.0.0.1:8783' . $path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADERFUNCTION => static function ($curl, $line) use (&$headers) { if (str_contains($line, ':')) { [$key, $value] = explode(':', $line, 2); $headers[strtolower(trim($key))] = trim($value); } return strlen($line); }]);
    if ($cookies) { curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie); curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie); }
    if ($post !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    $body = curl_exec($curl);
    if ($body === false) throw new RuntimeException(curl_error($curl));
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    unset($curl);
    return [$status, $headers, $body];
}
function blogToken(string $html): string { preg_match('/name="csrf" value="([a-f0-9]{64})"/', $html, $match); return $match[1] ?? ''; }

try {
    require __DIR__ . '/schema-checks.php';
    $blog = new Blog();
    $valid = ['title' => 'Prueba de tecnología & Veracruz', 'excerpt' => 'Resumen de prueba local.', 'content' => "Contenido único de prueba.\n\n## Subtítulo\n\nÁrboles y tecnología 😀", 'category' => 'tecnologia', 'image_alt' => 'Imagen de prueba', 'source_name' => 'Fuente de prueba', 'source_url' => 'https://example.com/nota'];
    foreach ([['title' => []], ['title' => str_repeat('á', 181)], ['content' => "\xFF"], ['category' => 'otra'], ['source_url' => 'javascript:alert(1)'], ['source_url' => 'https://user:secret@example.com/']] as $bad) {
        verifyBlog(Blog::validate(array_replace($valid, $bad))[1] !== [], 'Invalid article accepted');
    }
    rejectsBlog(fn () => Blog::imageData('<svg onload="alert(1)"></svg>'), 'SVG upload accepted');
    rejectsBlog(fn () => Blog::imageData(str_repeat('a', 4 * 1024 * 1024 + 1)), 'Oversized photo accepted');
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jD1sAAAAASUVORK5CYII=');
    $image = Blog::imageData($png);
    $id = $blog->save($valid, 'draft', 0, 0, $image);
    $draft = $blog->find($id);
    verifyBlog(count($blog->publicPosts()) === 0, 'Draft is public');
    verifyBlog($blog->image($image['id']) === null && $blog->image($image['id'], true)['content'] === $png, 'Draft image is public or damaged');
    $path = '/noticias/' . $draft['slug'] . '.html';
    verifyBlog((new Application())->handle('GET', $path)->status === 404, 'Draft route is public');
    $blog->save($valid, 'published', $id, 1);
    $published = $blog->find($id);
    verifyBlog($published['revision'] === 2 || (int) $published['revision'] === 2, 'Revision did not advance');
    verifyBlog($blog->image($image['id'])['content'] === $png, 'Published image is missing');
    $app = new Application();
    verifyBlog($app->handle('GET', $path)->status === 200, 'Published route missing');
    verifyBlog(str_contains($app->handle('GET', '/')->body, e($valid['title'])), 'Home does not show article');
    verifyBlog(str_contains($app->handle('GET', '/sitemap.xml')->body, $draft['slug']), 'Sitemap missing publication');
    verifyBlog(str_contains($app->handle('GET', $path)->body, 'BlogPosting'), 'Article schema missing');
    verifyBlog((new Search(new Site(new Service())))->find('árboles') !== [], 'Full article not searchable');
    verifyBlog(!str_contains($app->handle('GET', '/noticias.html?categoria=veracruz')->body, $draft['slug']), 'Category filter failed');
    verifyBlog($app->handle('GET', '/noticias.html?categoria[]=veracruz')->status === 404, 'Invalid category accepted');
    rejectsBlog(fn () => $blog->save($valid, 'draft', $id, 1), 'Stale revision overwrote publication');
    $badImage = Blog::imageData($png);
    rejectsBlog(fn () => $blog->save(array_replace($valid, ['image_alt' => '']), 'published', 0, 0, $badImage), 'Missing image description accepted');
    verifyBlog($blog->image($badImage['id'], true) === null, 'Failed transaction left an image');
    $attack = array_replace($valid, ['title' => '<script>alert(1)</script>', 'content' => '<img src=x onerror=alert(1)>']);
    $blog->save($attack, 'published', $id, 2);
    $body = (new Application())->handle('GET', $path)->body;
    verifyBlog(!str_contains($body, '<script>alert(1)</script>') && str_contains($body, '&lt;script&gt;'), 'Stored HTML injection');
    $blog->save($valid, 'draft', $id, 3);
    verifyBlog((new Application())->handle('GET', $path)->status === 404 && $blog->image($image['id']) === null, 'Unpublished article or photo remains public');
    verifyBlog(!str_contains((new Application())->handle('GET', '/sitemap.xml')->body, $draft['slug']), 'Draft remains in sitemap');
    verifyBlog((new Search(new Site(new Service())))->find('árboles') === [], 'Draft remains in search');

    $password = bin2hex(random_bytes(16));
    $query = $db->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, UTC_TIMESTAMP())');
    $query->execute(['qa_editor', password_hash($password, PASSWORD_DEFAULT)]);
    $process = proc_open([PHP_BINARY, '-S', '127.0.0.1:8783', 'router.php'], [0 => ['pipe', 'r'], 1 => ['file', $log, 'a'], 2 => ['file', $log, 'a']], $pipes, ROOT_PATH);
    $ready = false;
    for ($attempt = 0; $attempt < 30; $attempt++) { $socket = @fsockopen('127.0.0.1', 8783, $errno, $error, .1); if ($socket) { fclose($socket); $ready = true; break; } usleep(100000); }
    verifyBlog($ready, 'HTTP test server did not start');
    [$status, $headers, $body] = blogHttp('/admin/noticias');
    $token = blogToken($body);
    verifyBlog($status === 200 && strlen($token) === 64, 'Login page failed');
    verifyBlog($headers['cache-control'] === 'no-store' && $headers['x-robots-tag'] === 'noindex, nofollow', 'Admin caching/indexing headers missing');
    verifyBlog(str_contains(strtolower($headers['set-cookie'] ?? ''), 'httponly') && str_contains(strtolower($headers['set-cookie'] ?? ''), 'samesite=strict'), 'Session cookie flags missing');
    verifyBlog(!str_contains($body, 'analyticsConfig'), 'Admin has analytics');
    verifyBlog(blogHttp('/admin/noticias/entrar', ['csrf' => 'wrong', 'username' => 'qa_editor', 'password' => $password])[0] === 403, 'Login CSRF accepted');
    [$status] = blogHttp('/admin/noticias/entrar', ['csrf' => $token, 'username' => 'qa_editor', 'password' => $password]);
    verifyBlog($status === 303, 'Correct login failed');
    [$status, , $body] = blogHttp('/admin/noticias');
    $token = blogToken($body);
    verifyBlog($status === 200 && str_contains($body, 'Nueva noticia'), 'Dashboard missing');
    verifyBlog(blogHttp('/admin/noticias/guardar', $valid + ['csrf' => 'bad', 'id' => '0', 'revision' => '0', 'status' => 'published'])[0] === 403, 'Save CSRF accepted');
    verifyBlog(blogHttp('/admin/noticias/guardar', $valid + ['csrf' => $token, 'id' => 'invalid', 'revision' => '0', 'status' => 'published'])[0] === 400, 'Malformed edit ID created a new article');
    file_put_contents($dir . '/image.png', $png);
    $data = $valid + ['csrf' => $token, 'id' => '0', 'revision' => '0', 'status' => 'draft', 'photo' => new CURLFile($dir . '/image.png', 'image/png', 'photo.png')];
    verifyBlog(blogHttp('/admin/noticias/guardar', $data)[0] === 303, 'Multipart draft failed');
    $saved = $db->query('SELECT * FROM blog_posts ORDER BY id DESC LIMIT 1')->fetch();
    verifyBlog($saved['status'] === 'draft' && $saved['image_id'], 'Draft/photo not persisted');
    $edit = blogHttp('/admin/noticias/editar?id=' . $saved['id'])[2];
    verifyBlog(str_contains($edit, e($valid['title'])) && str_contains($edit, 'Actualizar publicación') === false, 'Saved fields not visible');
    verifyBlog(blogHttp('/admin/noticias/vista-previa?id=' . $saved['id'])[0] === 200, 'Private preview unavailable');
    verifyBlog(blogHttp('/noticias/imagen/' . $saved['image_id'], null, false)[0] === 404, 'Draft photo leaked by HTTP');
    verifyBlog(blogHttp('/admin/noticias/imagen/' . $saved['image_id'])[2] === $png, 'Private photo failed');
    $data = $valid + ['csrf' => $token, 'id' => (string) $saved['id'], 'revision' => '1', 'status' => 'published'];
    verifyBlog(blogHttp('/admin/noticias/guardar', $data)[0] === 303, 'Publishing failed');
    verifyBlog(blogHttp('/noticias/' . $saved['slug'] . '.html', null, false)[0] === 200, 'New publication unavailable');
    verifyBlog(blogHttp('/admin/noticias/guardar', $data)[0] === 409, 'Stale HTTP save accepted');
    verifyBlog(blogHttp('/admin/noticias/salir', ['csrf' => $token])[0] === 303, 'Logout failed');
    verifyBlog(str_contains(blogHttp('/admin/noticias/editar?id=' . $saved['id'])[2], 'Entrar al panel'), 'Logout did not revoke editor access');
    verifyBlog(blogHttp('/admin/noticias/imagen/' . $saved['image_id'])[2] !== $png, 'Private image bypassed authentication');
    $token = blogToken(blogHttp('/admin/noticias')[2]);
    for ($attempt = 0; $attempt < 8; $attempt++) $last = blogHttp('/admin/noticias/entrar', ['csrf' => $token, 'username' => 'qa_editor', 'password' => 'incorrect']);
    verifyBlog(str_contains($last[2], 'Demasiados intentos'), 'Persistent login throttle failed');
    require __DIR__ . '/crm-checks.php';
    echo "OK: $checks comprobaciones de CRM, usuarios, roles, revocación de sesiones, blog, SQL/PDO, transacciones, imágenes, borradores, publicación, MVC, login, CSRF y acceso privado.\n";
} finally {
    if (is_resource($process)) { proc_terminate($process); foreach ($pipes as $pipe) fclose($pipe); proc_close($process); }
    foreach (['blog_posts', 'blog_images', 'users', 'blog_login_attempts'] as $table) $db->exec('DELETE FROM `' . $table . '`');
}
