<?php
declare(strict_types=1);

namespace App\Services;

use Throwable;

final class BlogAuth
{
    public function __construct(private BlogDatabase $database) {}

    public function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name('clicomputer_crm');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            session_set_cookie_params(['lifetime' => 0, 'path' => '/admin',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true, 'samesite' => 'Strict']);
            session_start();
        }
        $_SESSION['blog_csrf'] ??= bin2hex(random_bytes(32));
    }

    public function token(): string { return $_SESSION['blog_csrf']; }
    public function checkToken($value): bool { return is_string($value) && hash_equals($this->token(), $value); }

    public function user(): ?array
    {
        if (!isset($_SESSION['blog_user']) || ($_SESSION['blog_activity'] ?? 0) < time() - 3600 || ($_SESSION['blog_started'] ?? 0) < time() - 28800) return null;
        $query = $this->database->connection()->prepare('SELECT * FROM users WHERE id = ?');
        $query->execute([$_SESSION['blog_user']]);
        $user = $query->fetch();
        if (!$user || !(int) $user['is_active'] || !in_array($user['role'], ['admin', 'editor'], true)
            || !hash_equals($_SESSION['blog_version'] ?? '', $this->fingerprint($user))) return null;
        $_SESSION['blog_activity'] = time();
        unset($user['password_hash']);
        return $user;
    }

    /** Límite persistente por IP, compartido entre sesiones y procesos. */
    private function allowLogin(): bool
    {
        $db = $this->database->connection();
        $key = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $now = time();
        $db->beginTransaction();
        try {
            $query = $db->prepare('DELETE FROM blog_login_attempts WHERE expires_at < ?');
            $query->execute([$now]);
            $query = $db->prepare('INSERT IGNORE INTO blog_login_attempts (address_hash, attempts, expires_at) VALUES (?, 0, ?)');
            $query->execute([$key, $now + 900]);
            $query = $db->prepare('SELECT attempts FROM blog_login_attempts WHERE address_hash = ? FOR UPDATE');
            $query->execute([$key]);
            $allowed = (int) $query->fetchColumn() < 8;
            if ($allowed) {
                $query = $db->prepare('UPDATE blog_login_attempts SET attempts = attempts + 1 WHERE address_hash = ?');
                $query->execute([$key]);
            }
            $db->commit();
            return $allowed;
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }
    }

    public function login($username, $password): string
    {
        if (!$this->allowLogin()) return 'Demasiados intentos. Espera 15 minutos antes de volver a intentarlo.';
        $username = is_string($username) ? trim($username) : '';
        $password = is_string($password) ? $password : '';
        if (strlen($username) > 80 || strlen($password) > 1024) return 'Usuario o contraseña incorrectos.';
        $query = $this->database->connection()->prepare('SELECT * FROM users WHERE username = ?');
        $query->execute([$username]);
        $user = $query->fetch();
        // El hash de comparación evita una salida rápida cuando el usuario no existe.
        $hash = $user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        if (!password_verify($password, $hash) || !$user || !(int) $user['is_active'] || !in_array($user['role'], ['admin', 'editor'], true)) return 'Usuario o contraseña incorrectos.';
        session_regenerate_id(true);
        $_SESSION = ['blog_user' => (int) $user['id'], 'blog_version' => $this->fingerprint($user),
            'blog_activity' => time(), 'blog_started' => time(), 'blog_csrf' => bin2hex(random_bytes(32))];
        return '';
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['blog_csrf'] = bin2hex(random_bytes(32));
    }

    private function fingerprint(array $user): string
    {
        return hash('sha256', $user['password_hash'] . ':' . $user['auth_version'] . ':' . $user['role'] . ':' . $user['is_active']);
    }
}
