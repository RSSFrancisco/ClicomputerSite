<?php
declare(strict_types=1);

// Instalación explícita por CLI. Nunca ejecuta migraciones desde una visita al sitio.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';

try {
    $db = (new App\Services\BlogDatabase())->connection();
    if (in_array('--check', $argv, true)) {
        foreach (['users', 'blog_posts', 'blog_images', 'blog_login_attempts', 'schema_migrations'] as $table) $db->query('SELECT 1 FROM `' . $table . '` LIMIT 1');
        $db->query('SELECT role, is_active, revision, auth_version, display_name, email FROM users LIMIT 1');
        echo "OK: conexión MySQL y tablas del CRM y blog disponibles.\n";
        exit;
    }
    App\Services\AdminSchema::migrate($db);
    $reset = in_array('--reset-password', $argv, true);
    if (!$reset && (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
        echo "OK: esquema disponible. El usuario existente se conserva.\n";
        exit;
    }
    $username = $reset ? ($argv[2] ?? '') : ($argv[1] ?? 'editor');
    if (!preg_match('/^[a-zA-Z0-9._-]{3,80}$/D', $username)) throw new RuntimeException('Usa un nombre de editor de 3 a 80 letras, números, puntos, guiones o guiones bajos.');
    $password = bin2hex(random_bytes(12));
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($reset) {
        $query = $db->prepare('UPDATE users SET password_hash = ?, auth_version = auth_version + 1, revision = revision + 1 WHERE username = ?');
        $query->execute([$hash, $username]);
        if (!$query->rowCount()) throw new RuntimeException('No existe ese usuario editor.');
    } else {
        $query = $db->prepare("INSERT INTO users (username, password_hash, role, created_at) VALUES (?, ?, 'admin', UTC_TIMESTAMP())");
        $query->execute([$username, $hash]);
    }
    echo "CRM: /admin\nBlog: /admin/noticias\nUsuario: $username\nContraseña: $password\nGuárdala en tu gestor de contraseñas. No se guarda en texto plano en la base de datos.\n";
} catch (Throwable $error) {
    // PDO puede incluir datos de la conexión: nunca imprimir su excepción completa.
    fwrite(STDERR, $error instanceof PDOException ? "No se pudo preparar el blog. Revisa la conexión y los permisos de MySQL.\n" : $error->getMessage() . "\n");
    exit(1);
}
