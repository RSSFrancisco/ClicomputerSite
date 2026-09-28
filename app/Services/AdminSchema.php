<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;
use Throwable;

/** Instalación y migraciones sin borrar datos, invocadas exclusivamente por CLI. */
final class AdminSchema
{
    private const LEGACY_TABLES = [
        'clicomputer_blog_users' => 'users',
        'clicomputer_blog_images' => 'blog_images',
        'clicomputer_blog_posts' => 'blog_posts',
        'clicomputer_blog_login_attempts' => 'blog_login_attempts',
        'clicomputer_schema_migrations' => 'schema_migrations',
    ];

    public static function migrate(PDO $db): void
    {
        $lock = 'clicomputer_crm_' . substr(hash('sha256', (string) $db->query('SELECT DATABASE()')->fetchColumn()), 0, 32);
        $query = $db->prepare('SELECT GET_LOCK(?, 10)');
        $query->execute([$lock]);
        if ((int) $query->fetchColumn() !== 1) throw new RuntimeException('Hay otra actualización del CRM en curso. Inténtalo de nuevo.');
        try {
            self::renameLegacyTables($db);
            // Renombrar antes de crear: evita tablas nuevas vacías junto a los datos anteriores.
            foreach (explode(';', (string) file_get_contents(ROOT_PATH . '/database/blog.sql')) as $sql) {
                if (trim($sql) !== '') $db->exec($sql);
            }
            $db->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(80) NOT NULL PRIMARY KEY, applied_at DATETIME NOT NULL) ENGINE=InnoDB');
            $columns = $db->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
            foreach ([
                'display_name' => "VARCHAR(120) NOT NULL DEFAULT ''",
                'email' => "VARCHAR(254) NOT NULL DEFAULT ''",
                'role' => "VARCHAR(20) NOT NULL DEFAULT 'editor'",
                'is_active' => 'TINYINT UNSIGNED NOT NULL DEFAULT 1',
                'revision' => 'INT UNSIGNED NOT NULL DEFAULT 1',
                'auth_version' => 'INT UNSIGNED NOT NULL DEFAULT 1',
            ] as $column => $definition) {
                if (!in_array($column, $columns, true)) $db->exec('ALTER TABLE users ADD COLUMN ' . $column . ' ' . $definition);
            }
            $db->beginTransaction();
            if (!$db->query("SELECT version FROM schema_migrations WHERE version = 'crm_users_v1'")->fetchColumn()) {
                // El primer editor existente se convierte en administrador al actualizar.
                if (!(int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1")->fetchColumn()) {
                    $db->exec("UPDATE users SET role = 'admin', auth_version = auth_version + 1 WHERE is_active = 1 ORDER BY id LIMIT 1");
                }
                $db->exec("UPDATE users SET display_name = username WHERE display_name = ''");
                $db->exec("INSERT INTO schema_migrations (version, applied_at) VALUES ('crm_users_v1', UTC_TIMESTAMP())");
            }
            $db->commit();
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        } finally {
            $query = $db->prepare('SELECT RELEASE_LOCK(?)');
            $query->execute([$lock]);
        }
    }

    private static function renameLegacyTables(PDO $db): void
    {
        $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $renames = [];
        foreach (self::LEGACY_TABLES as $old => $new) {
            if (!in_array($old, $tables, true)) continue;
            if (in_array($new, $tables, true)) {
                throw new RuntimeException("Existen las tablas $old y $new. No se modificó ninguna tabla. Revisa el conflicto antes de continuar.");
            }
            $renames[] = '`' . $old . '` TO `' . $new . '`';
        }
        // Nombres fijos y una sola operación: conserva datos, claves y relaciones.
        if ($renames) $db->exec('RENAME TABLE ' . implode(', ', $renames));
    }
}
