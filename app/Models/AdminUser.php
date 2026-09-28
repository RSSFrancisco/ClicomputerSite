<?php
declare(strict_types=1);

namespace App\Models;

use App\Services\BlogDatabase;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class AdminUser
{
    public const ROLES = ['admin' => 'Administrador', 'editor' => 'Editor'];
    private const FIELDS = 'id, username, display_name, email, role, is_active, revision, auth_version, created_at';

    public function __construct(private BlogDatabase $database) {}

    public function find(int $id): ?array
    {
        $query = $this->database->connection()->prepare('SELECT ' . self::FIELDS . ' FROM users WHERE id = ?');
        $query->execute([$id]);
        return $query->fetch() ?: null;
    }

    public function listing(string $search, string $state, int $page): array
    {
        $where = ' WHERE (username LIKE ? OR display_name LIKE ? OR email LIKE ?)';
        $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
        $where = str_replace('LIKE ?', "LIKE ? ESCAPE '!'", $where);
        if ($state !== '') $where .= ' AND is_active = ' . ($state === 'active' ? '1' : '0');
        $query = $this->database->connection()->prepare('SELECT COUNT(*) FROM users' . $where);
        $query->execute([$like, $like, $like]);
        $total = (int) $query->fetchColumn();
        $pages = max(1, (int) ceil($total / 20));
        $page = min($page, $pages);
        $query = $this->database->connection()->prepare('SELECT ' . self::FIELDS . ' FROM users' . $where . ' ORDER BY is_active DESC, display_name, username LIMIT 20 OFFSET ' . (($page - 1) * 20));
        $query->execute([$like, $like, $like]);
        return ['users' => $query->fetchAll(), 'userTotal' => $total, 'userPage' => $page, 'userPages' => $pages];
    }

    public function counts(): array
    {
        return $this->database->connection()->query("SELECT COUNT(*) AS total, COALESCE(SUM(is_active = 1), 0) AS active FROM users")->fetch();
    }

    public static function passwordError($password): string
    {
        return !is_string($password) || !preg_match('//u', $password) || str_contains($password, "\0") || preg_match_all('/./us', $password) < 12 || strlen($password) > 72
            ? 'Usa al menos 12 caracteres y un máximo de 72 bytes. Puedes escribir una frase.' : '';
    }

    public static function validate(array $input, bool $new): array
    {
        $values = $errors = [];
        foreach (['username' => 80, 'display_name' => 120, 'email' => 254, 'role' => 20] as $field => $limit) {
            $value = $input[$field] ?? '';
            $values[$field] = is_string($value) && preg_match('//u', $value) ? trim($value) : '';
            if (!is_string($value) || !preg_match('//u', $value) || preg_match_all('/./us', $values[$field]) > $limit || preg_match('/[\x00-\x1f\x7f]/', $values[$field])) $errors[$field] = 'Revisa este campo y su longitud.';
        }
        if (!preg_match('/^[a-zA-Z0-9._-]{3,80}$/D', $values['username'])) $errors['username'] = 'Usa de 3 a 80 letras, números, puntos, guiones o guiones bajos.';
        if ($values['display_name'] === '') $errors['display_name'] = 'Escribe el nombre de la persona.';
        if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Escribe un correo válido.';
        if (!isset(self::ROLES[$values['role']])) $errors['role'] = 'Elige un rol válido.';
        $active = $input['is_active'] ?? null;
        if (!in_array($active, ['0', '1'], true)) $errors['is_active'] = 'Elige el estado de acceso.';
        $values['is_active'] = $active === '1' ? 1 : 0;
        $password = $input['password'] ?? '';
        if ($new || $password !== '') {
            if ($error = self::passwordError($password)) $errors['password'] = $error;
            elseif (!is_string($input['password_confirmation'] ?? null) || !hash_equals($password, $input['password_confirmation'])) $errors['password_confirmation'] = 'Las contraseñas no coinciden.';
        }
        return [$values, $errors];
    }

    /** Bloquea usuarios en orden estable para serializar cambios de permisos y proteger al último administrador. */
    public function save(array $input, int $id, int $revision, array $actor): int
    {
        [$values, $errors] = self::validate($input, $id === 0);
        if ($errors) throw new RuntimeException('Revisa los datos del usuario.');
        $db = $this->database->connection();
        $db->beginTransaction();
        try {
            $rows = $db->query('SELECT * FROM users ORDER BY id FOR UPDATE')->fetchAll(PDO::FETCH_UNIQUE);
            $current = $rows[$actor['id']] ?? null;
            if (!$current || !$current['is_active'] || $current['role'] !== 'admin' || (int) $current['auth_version'] !== (int) $actor['auth_version']) throw new RuntimeException('Tu acceso cambió. Vuelve a iniciar sesión.');
            $old = $rows[$id] ?? null;
            if ($id && (!$old || (int) $old['revision'] !== $revision)) throw new RuntimeException('Este usuario cambió en otra sesión. Vuelve a abrirlo antes de guardar.');
            if ($id === (int) $actor['id'] && ($values['role'] !== 'admin' || !$values['is_active'])) throw new RuntimeException('No puedes desactivar tu propia cuenta ni quitarte el rol de administrador.');
            $password = $input['password'] ?? '';
            if ($id === (int) $actor['id'] && $password !== '') throw new RuntimeException('Cambia tu contraseña desde Mi cuenta, confirmando tu contraseña actual.');
            if ($old && $old['role'] === 'admin' && $old['is_active'] && ($values['role'] !== 'admin' || !$values['is_active'])) {
                $activeAdmins = array_filter($rows, static fn ($row) => $row['role'] === 'admin' && $row['is_active']);
                if (count($activeAdmins) <= 1) throw new RuntimeException('Debe permanecer al menos un administrador activo.');
            }
            if ($id) {
                $revoke = $password !== '' || $old['role'] !== $values['role'] || (int) $old['is_active'] !== $values['is_active'] || $old['username'] !== $values['username'];
                $query = $db->prepare('UPDATE users SET username = ?, display_name = ?, email = ?, role = ?, is_active = ?, password_hash = ?, revision = revision + 1, auth_version = auth_version + ? WHERE id = ?');
                $query->execute([$values['username'], $values['display_name'], $values['email'], $values['role'], $values['is_active'], $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : $old['password_hash'], $revoke ? 1 : 0, $id]);
            } else {
                $query = $db->prepare('INSERT INTO users (username, display_name, email, role, is_active, password_hash, created_at) VALUES (?, ?, ?, ?, ?, ?, UTC_TIMESTAMP())');
                $query->execute([$values['username'], $values['display_name'], $values['email'], $values['role'], $values['is_active'], password_hash($password, PASSWORD_DEFAULT)]);
                $id = (int) $db->lastInsertId();
            }
            $db->commit();
            return $id;
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            if ($error instanceof PDOException && (int) ($error->errorInfo[1] ?? 0) === 1062) throw new RuntimeException('Ese nombre de usuario ya está en uso. Elige otro.');
            throw $error;
        }
    }

    public function changePassword(array $actor, string $currentPassword, string $password): void
    {
        if ($error = self::passwordError($password)) throw new RuntimeException($error);
        $db = $this->database->connection();
        $db->beginTransaction();
        try {
            $query = $db->prepare('SELECT * FROM users WHERE id = ? FOR UPDATE');
            $query->execute([$actor['id']]);
            $user = $query->fetch();
            if (!$user || !$user['is_active'] || (int) $user['auth_version'] !== (int) $actor['auth_version'] || !password_verify($currentPassword, $user['password_hash'])) throw new RuntimeException('La contraseña actual no es correcta o tu sesión cambió.');
            if (password_verify($password, $user['password_hash'])) throw new RuntimeException('Elige una contraseña diferente de la actual.');
            $query = $db->prepare('UPDATE users SET password_hash = ?, auth_version = auth_version + 1, revision = revision + 1 WHERE id = ?');
            $query->execute([password_hash($password, PASSWORD_DEFAULT), $actor['id']]);
            $db->commit();
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }
    }
}
