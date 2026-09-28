<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminView;
use App\Core\Response;
use App\Models\AdminUser;
use App\Models\Blog;
use App\Models\Site;
use App\Services\BlogAuth;
use Throwable;

/** Única puerta de entrada de todas las rutas privadas. */
final class AdminController
{
    public function __construct(private Blog $blog, private Site $site, private PageController $pages) {}

    public function handle(string $method, string $path, array $params, array $input, array $files): Response
    {
        if (!in_array($method, ['GET', 'HEAD', 'POST'], true)) return new Response('', 405, AdminView::HEADERS + ['Allow' => 'GET, HEAD, POST']);
        $local = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
        if (PHP_SAPI !== 'cli' && !$local && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) return AdminView::redirect($this->site->settings()['url'] . '/admin');
        $view = new AdminView();
        try {
            if (!$this->blog->database->configured()) return $view->notice('El CRM aún no está configurado. Completa la conexión de MySQL y la instalación del panel.', 503);
            $this->blog->database->connection()->query('SELECT role, is_active, auth_version, revision FROM users LIMIT 1');
            $auth = new BlogAuth($this->blog->database);
            $auth->start();
            $user = $auth->user();
            $view = new AdminView($user, $auth->token());
            if ($method === 'POST' && !$auth->checkToken($input['csrf'] ?? null)) return $view->notice('La sesión del formulario venció. Recarga la página e inténtalo de nuevo.', 403);
            if (!$user) {
                $message = ($params['clave'] ?? '') === '1' ? 'Contraseña actualizada. Inicia sesión con tu nueva contraseña.' : '';
                if ($method === 'POST' && in_array($path, ['/admin/entrar', '/admin/noticias/entrar'], true)) {
                    $message = $auth->login($input['username'] ?? '', $input['password'] ?? '');
                    if ($message === '') return AdminView::redirect('/admin');
                    return $view->render('admin-login', 'Entra a tu CRM', ['editorMessage' => $message], 401);
                }
                return $view->render('admin-login', 'Entra a tu CRM', ['editorMessage' => $message], $method === 'POST' ? 401 : 200);
            }
            if ($method === 'POST' && in_array($path, ['/admin/salir', '/admin/noticias/salir'], true)) {
                $auth->logout();
                return AdminView::redirect('/admin');
            }
            $users = new AdminUser($this->blog->database);
            if ($path === '/admin/mi-cuenta') return (new UserAdminController($users, $view, $user))->account($method, $input, $auth);
            if ($path === '/admin/usuarios' || str_starts_with($path, '/admin/usuarios/')) {
                if ($user['role'] !== 'admin') return $view->notice('Tu rol de editor permite administrar el blog. Solo un administrador puede gestionar usuarios.', 403);
                return (new UserAdminController($users, $view, $user))->handle($method, $path, $params, $input);
            }
            if ($path === '/admin/noticias' || str_starts_with($path, '/admin/noticias/')) return (new BlogAdminController($this->blog, $this->site, $this->pages, $view))->handle($method, $path, $params, $input, $files);
            if (!in_array($path, ['/admin', '/admin/'], true)) return $view->notice('No se encontró esta página del CRM.', 404);
            if ($method === 'POST') return new Response('', 405, AdminView::HEADERS + ['Allow' => 'GET, HEAD']);
            $posts = $this->blog->all();
            $published = count(array_filter($posts, static fn ($post) => $post['status'] === 'published'));
            return $view->render('admin-dashboard', 'Vista general', ['recentPosts' => array_slice($posts, 0, 5),
                'postCounts' => ['published' => $published, 'draft' => count($posts) - $published],
                'userCounts' => $user['role'] === 'admin' ? $users->counts() : null]);
        } catch (Throwable) {
            error_log('Clicomputer: crm_unavailable');
            return $view->notice('El CRM no está disponible temporalmente. Revisa la conexión de MySQL y que la actualización del panel esté instalada.', 503);
        }
    }
}
