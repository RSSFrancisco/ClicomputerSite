<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminView;
use App\Core\Response;
use App\Models\AdminUser;
use App\Services\BlogAuth;
use PDOException;
use RuntimeException;

final class UserAdminController
{
    public function __construct(private AdminUser $users, private AdminView $view, private array $actor) {}

    public function handle(string $method, string $path, array $params, array $input): Response
    {
        if ($path === '/admin/usuarios/guardar' && $method === 'POST') return $this->save($input);
        if ($method === 'POST') return new Response('', 405, AdminView::HEADERS + ['Allow' => 'GET, HEAD']);
        if ($path === '/admin/usuarios/nuevo') return $this->form(['id' => 0, 'revision' => 0, 'username' => '', 'display_name' => '', 'email' => '', 'role' => 'editor', 'is_active' => 1]);
        if ($path === '/admin/usuarios/editar') {
            $id = $this->id($params['id'] ?? null);
            $user = $id ? $this->users->find($id) : null;
            return $user ? $this->form($user) : $this->view->notice('No se encontró este usuario.', 404);
        }
        if (!in_array($path, ['/admin/usuarios', '/admin/usuarios/'], true)) return $this->view->notice('Página no encontrada.', 404);
        $search = $params['q'] ?? '';
        $state = $params['estado'] ?? '';
        $page = $params['pagina'] ?? '1';
        if (!is_string($search) || strlen($search) > 160 || !preg_match('//u', $search) || !in_array($state, ['', 'active', 'inactive'], true) || !$this->id($page)) return $this->view->notice('Los filtros no son válidos.', 400);
        return $this->view->render('admin-users', 'Usuarios', $this->users->listing(trim($search), $state, (int) $page) + [
            'adminSection' => 'usuarios', 'userSearch' => $search, 'userState' => $state,
            'editorMessage' => ($params['guardado'] ?? '') === '1' ? 'Usuario guardado.' : '']);
    }

    private function id($value): int { return is_scalar($value) && preg_match('/^[1-9][0-9]{0,9}$/D', (string) $value) ? (int) $value : 0; }

    private function form(array $values, array $errors = [], string $message = '', int $status = 200): Response
    {
        return $this->view->render('admin-user-edit', $values['id'] ? 'Editar usuario' : 'Nuevo usuario', [
            'adminSection' => 'usuarios', 'userValues' => $values, 'editorErrors' => $errors, 'editorMessage' => $message], $status);
    }

    private function save(array $input): Response
    {
        foreach (['id', 'revision'] as $field) if (!is_string($input[$field] ?? null) || !preg_match('/^(?:0|[1-9][0-9]{0,9})$/D', $input[$field])) return $this->view->notice('El usuario o la revisión no son válidos.', 400);
        $id = (int) $input['id'];
        if ($id && !$this->users->find($id)) return $this->view->notice('No se encontró este usuario.', 404);
        [$values, $errors] = AdminUser::validate($input, $id === 0);
        $values += ['id' => $id, 'revision' => (int) $input['revision']];
        if ($errors) return $this->form($values, $errors, 'Revisa los campos indicados. Vuelve a escribir la contraseña si deseas establecerla.', 422);
        try {
            $this->users->save($input, $id, (int) $input['revision'], $this->actor);
            return AdminView::redirect('/admin/usuarios?guardado=1');
        } catch (PDOException) {
            return $this->form($values, [], 'No pudimos guardar el usuario. Inténtalo nuevamente.', 503);
        } catch (RuntimeException $error) {
            return $this->form($values, [], $error->getMessage(), 409);
        }
    }

    public function account(string $method, array $input, BlogAuth $auth): Response
    {
        $message = '';
        $status = 200;
        if ($method === 'POST') {
            $password = $input['password'] ?? null;
            $current = $input['current_password'] ?? null;
            if (!is_string($current) || strlen($current) > 1024) $message = 'Escribe tu contraseña actual.';
            elseif ($error = AdminUser::passwordError($password)) $message = $error;
            elseif (!is_string($input['password_confirmation'] ?? null) || !hash_equals($password, $input['password_confirmation'])) $message = 'Las contraseñas nuevas no coinciden.';
            else {
                try {
                    $this->users->changePassword($this->actor, $current, $password);
                    $auth->logout();
                    return AdminView::redirect('/admin?clave=1');
                } catch (PDOException) { $message = 'No pudimos cambiar la contraseña. Inténtalo de nuevo.'; }
                catch (RuntimeException $error) { $message = $error->getMessage(); }
            }
            $status = 422;
        }
        return $this->view->render('admin-account', 'Mi cuenta', ['adminSection' => 'cuenta', 'editorMessage' => $message], $status);
    }
}
