<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Blog;
use App\Models\Site;
use App\Core\AdminView;
use PDOException;
use RuntimeException;

final class BlogAdminController
{
    public function __construct(private Blog $blog, private Site $site, private PageController $pages, private AdminView $view) {}

    /** Recibe únicamente peticiones autenticadas por el controlador del CRM. */
    public function handle(string $method, string $path, array $params, array $input, array $files): Response
    {
        if (preg_match('~^/admin/noticias/imagen/([a-f0-9]{32})$~D', $path, $match) && $method !== 'POST') {
            $response = (new BlogController($this->blog, $this->site, $this->pages))->image($match[1], true);
            $response->headers += AdminView::HEADERS;
            return $response;
        }
        if ($path === '/admin/noticias/guardar' && $method === 'POST') return $this->save($input, $files);
        if ($method === 'POST') return new Response('', 405, AdminView::HEADERS + ['Allow' => 'GET, HEAD']);
        if ($path === '/admin/noticias/nueva') return $this->render('editor', ['editorValues' => $this->emptyPost()]);
        if (in_array($path, ['/admin/noticias/editar', '/admin/noticias/vista-previa'], true)) {
            $id = $this->id($params['id'] ?? null);
            $post = $id ? $this->blog->find($id) : null;
            if (!$post) return $this->render('notice', ['editorMessage' => 'No se encontró esta noticia.'], 404);
            return $this->render($path === '/admin/noticias/editar' ? 'editor' : 'preview', ['editorValues' => $post]);
        }
        if ($path !== '/admin/noticias' && $path !== '/admin/noticias/') return $this->render('notice', ['editorMessage' => 'Página no encontrada.'], 404);
        return $this->render('dashboard', ['editorPosts' => $this->blog->all(), 'editorMessage' => ($params['guardado'] ?? '') === '1' ? 'Noticia guardada.' : '']);
    }

    private function id($value): int
    {
        return is_scalar($value) && preg_match('/^[1-9][0-9]{0,9}$/D', (string) $value) ? (int) $value : 0;
    }

    private function emptyPost(): array
    {
        return ['id' => 0, 'revision' => 0, 'title' => '', 'excerpt' => '', 'content' => '', 'category' => 'tecnologia',
            'status' => 'draft', 'image_id' => null, 'image_alt' => '', 'source_name' => '', 'source_url' => ''];
    }

    private function save(array $input, array $files): Response
    {
        foreach (['id', 'revision'] as $key) {
            if (!is_string($input[$key] ?? null) || !preg_match('/^(?:0|[1-9][0-9]{0,9})$/D', $input[$key])) {
                return $this->render('notice', ['editorMessage' => 'El identificador de la noticia no es válido. Vuelve a abrir el editor.'], 400);
            }
        }
        $id = $this->id($input['id'] ?? '0');
        $old = $id ? $this->blog->find($id) : null;
        if ($id && !$old) return $this->render('notice', ['editorMessage' => 'No se encontró esta noticia.'], 404);
        [$values, $errors] = Blog::validate($input);
        $values += ['id' => $id, 'revision' => $this->id($input['revision'] ?? '0'),
            'status' => $old['status'] ?? 'draft', 'image_id' => $old['image_id'] ?? null];
        $status = $input['status'] ?? '';
        if (!in_array($status, ['draft', 'published'], true)) $errors['status'] = 'Elige Guardar borrador o Publicar.';
        $image = null;
        try {
            $upload = $files['photo'] ?? ['error' => UPLOAD_ERR_NO_FILE];
            if (!is_array($upload) || !is_int($upload['error'] ?? null)) throw new RuntimeException('No pudimos recibir la foto. Selecciónala de nuevo.');
            if ($upload['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($upload['error'] !== UPLOAD_ERR_OK || !is_string($upload['tmp_name'] ?? null) || !is_uploaded_file($upload['tmp_name']) || filesize($upload['tmp_name']) > 4 * 1024 * 1024) throw new RuntimeException('No pudimos subir la foto. Usa una imagen de hasta 4 MB.');
                $image = Blog::imageData((string) file_get_contents($upload['tmp_name']));
            }
            if (($image || ($values['image_id'] && ($input['remove_image'] ?? '') !== '1')) && $values['image_alt'] === '') $errors['image_alt'] = 'Describe brevemente la foto.';
        } catch (RuntimeException $error) { $errors['photo'] = $error->getMessage(); }
        if ($errors) return $this->render('editor', ['editorValues' => $values, 'editorErrors' => $errors,
            'editorMessage' => 'Revisa los campos indicados. Si elegiste una foto nueva, selecciónala otra vez.'], 422);
        try {
            $this->blog->save($values, $status, $id, $values['revision'], $image, ($input['remove_image'] ?? '') === '1');
            return $this->redirect('/admin/noticias?guardado=1');
        } catch (PDOException) {
            return $this->render('editor', ['editorValues' => $values, 'editorMessage' => 'No pudimos guardar. Conservamos tu texto aquí; inténtalo de nuevo y vuelve a seleccionar la foto.'], 503);
        } catch (RuntimeException $error) {
            return $this->render('editor', ['editorValues' => $values, 'editorMessage' => $error->getMessage() . ' Si elegiste una foto nueva, selecciónala otra vez.'], 409);
        }
    }

    private function redirect(string $url): Response { return AdminView::redirect($url); }

    private function render(string $screen, array $data = [], int $status = 200): Response
    {
        $title = match ($screen) {
            'editor' => !empty($data['editorValues']['id']) ? 'Editar noticia' : 'Nueva noticia',
            'preview' => 'Vista previa',
            default => 'Blog y noticias',
        };
        return $this->view->render('blog-admin', $title, $data + ['adminSection' => 'noticias',
            'editorScreen' => $screen, 'editorPosts' => []], $status);
    }
}
