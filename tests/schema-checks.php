<?php
// Se ejecuta solo dentro del conjunto de integración y su base desechable vacía.
if (!isset($db, $checks) || getenv('CLICOMPUTER_BLOG_TEST') !== '1' || !str_ends_with((string) $db->query('SELECT DATABASE()')->fetchColumn(), '_test')) throw new RuntimeException('Ejecuta tests/blog.php para probar las migraciones.');
$schemaPairs = [
    'users' => 'clicomputer_blog_users',
    'blog_images' => 'clicomputer_blog_images',
    'blog_posts' => 'clicomputer_blog_posts',
    'blog_login_attempts' => 'clicomputer_blog_login_attempts',
    'schema_migrations' => 'clicomputer_schema_migrations',
];
$schemaPassword = bin2hex(random_bytes(16));
$query = $db->prepare("INSERT INTO users (username, display_name, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP())");
$query->execute(['migration_admin', 'Administrador existente', password_hash($schemaPassword, PASSWORD_DEFAULT), 'admin', 1]);
$query->execute(['migration_editor', 'Editor inactivo', password_hash($schemaPassword, PASSWORD_DEFAULT), 'editor', 0]);
$schemaBlog = new App\Models\Blog();
$schemaPhoto = App\Models\Blog::imageData(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jD1sAAAAASUVORK5CYII='));
$schemaPost = $schemaBlog->save(['title' => 'Noticia antes de migrar', 'excerpt' => 'Resumen conservado', 'content' => 'Contenido conservado', 'category' => 'veracruz', 'image_alt' => 'Foto de prueba', 'source_name' => '', 'source_url' => ''], 'published', 0, 0, $schemaPhoto);
$db->prepare('INSERT INTO blog_login_attempts (address_hash, attempts, expires_at) VALUES (?, 3, ?)')->execute([hash('sha256', 'schema-fixture'), time() + 900]);
$schemaBefore = [];
foreach ($schemaPairs as $table => $old) $schemaBefore[$table] = $db->query('SELECT * FROM `' . $table . '` ORDER BY 1')->fetchAll();
// Reconstruye los nombres de una instalación anterior con noticias, foto y usuarios reales de prueba.
$schemaRenames = [];
foreach ($schemaPairs as $table => $old) $schemaRenames[] = '`' . $table . '` TO `' . $old . '`';
$db->exec('RENAME TABLE ' . implode(', ', $schemaRenames));
// Un destino ocupado debe detener toda la operación antes de modificar cualquier tabla.
$db->exec('CREATE TABLE users LIKE clicomputer_blog_users');
try {
    rejectsBlog(fn () => App\Services\AdminSchema::migrate($db), 'Migration overwrote a conflicting users table');
    $schemaTables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    verifyBlog(count(array_intersect(array_values($schemaPairs), $schemaTables)) === count($schemaPairs), 'Conflict caused a partial rename');
    verifyBlog($schemaBefore['users'] === $db->query('SELECT * FROM clicomputer_blog_users ORDER BY 1')->fetchAll(), 'Conflict changed legacy users');
    verifyBlog((int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0, 'Conflict merged accounts automatically');
} finally {
    // Solo la tabla de conflicto vacía creada por esta prueba.
    $db->exec('DROP TABLE users');
}
App\Services\AdminSchema::migrate($db);
$schemaTables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($schemaPairs as $table => $old) {
    verifyBlog(in_array($table, $schemaTables, true) && !in_array($old, $schemaTables, true), 'Table not renamed: ' . $table);
    verifyBlog($schemaBefore[$table] === $db->query('SELECT * FROM `' . $table . '` ORDER BY 1')->fetchAll(), 'Migration changed rows in ' . $table);
}
verifyBlog(password_verify($schemaPassword, $db->query("SELECT password_hash FROM users WHERE username = 'migration_admin'")->fetchColumn()), 'Migration changed password');
verifyBlog((new App\Models\Blog())->find($schemaPost)['image_id'] === $schemaPhoto['id'], 'Post lost photo reference');
verifyBlog((new App\Models\Blog())->image($schemaPhoto['id'])['content'] === $schemaPhoto['content'], 'Photo content changed');
rejectsBlog(fn () => $db->exec("UPDATE blog_posts SET image_id = 'ffffffffffffffffffffffffffffffff' WHERE id = $schemaPost"), 'Renaming disabled the image foreign key');
App\Services\AdminSchema::migrate($db);
foreach ($schemaPairs as $table => $old) verifyBlog($schemaBefore[$table] === $db->query('SELECT * FROM `' . $table . '` ORDER BY 1')->fetchAll(), 'Second migration changed ' . $table);
foreach (['blog_posts', 'blog_images', 'users', 'blog_login_attempts'] as $table) $db->exec('DELETE FROM `' . $table . '`');
