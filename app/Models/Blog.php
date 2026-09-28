<?php
declare(strict_types=1);

namespace App\Models;

use App\Services\BlogDatabase;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

final class Blog
{
    public const CATEGORIES = ['tecnologia' => 'Tecnología', 'veracruz' => 'Veracruz'];
    private ?array $published = null;
    private bool $unavailable = false;

    public BlogDatabase $database;

    public function __construct(?BlogDatabase $database = null) { $this->database = $database ?? new BlogDatabase(); }

    public function publicPosts(): array
    {
        if ($this->published !== null) return $this->published;
        if (!$this->database->configured()) return $this->published = [];
        try {
            return $this->published = $this->database->connection()->query("SELECT * FROM blog_posts WHERE status = 'published' AND published_at <= UTC_TIMESTAMP() ORDER BY published_at DESC, id DESC")->fetchAll();
        } catch (Throwable) {
            $this->unavailable = true;
            error_log('Clicomputer: blog_database_unavailable');
            return $this->published = [];
        }
    }

    public function unavailable(): bool { $this->publicPosts(); return $this->unavailable; }

    public static function page(array $post): array
    {
        return [
            'file' => 'noticias/' . $post['slug'] . '.html', 'kind' => 'news',
            'title' => $post['title'] . ' | Clicomputer Noticias', 'label' => $post['title'],
            'headline' => $post['title'], 'description' => $post['excerpt'], 'intro' => $post['excerpt'],
            'sections' => ['news-article'], 'parent' => ['file' => 'noticias.html', 'label' => 'Noticias'],
            'post' => $post, 'news_content' => $post['content'], 'icon' => 'bi-newspaper',
        ];
    }

    public static function date(string $date): string
    {
        $value = (new DateTimeImmutable($date, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('America/Mexico_City'));
        $months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        return $value->format('j') . ' ' . $months[(int) $value->format('n') - 1] . ' ' . $value->format('Y');
    }

    public function all(): array
    {
        return $this->database->connection()->query('SELECT id, title, category, status, revision, published_at, updated_at, slug FROM blog_posts ORDER BY updated_at DESC, id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $query = $this->database->connection()->prepare('SELECT * FROM blog_posts WHERE id = ?');
        $query->execute([$id]);
        return $query->fetch() ?: null;
    }

    public static function validate(array $input): array
    {
        $values = $errors = [];
        foreach (['title' => 180, 'excerpt' => 320, 'content' => 50000, 'category' => 20, 'image_alt' => 200, 'source_name' => 120, 'source_url' => 1000] as $field => $limit) {
            $value = is_string($input[$field] ?? null) ? trim($input[$field]) : '';
            if (!preg_match('//u', $value) || str_contains($value, "\0")) { $value = ''; $errors[$field] = 'Usa texto válido.'; }
            if (preg_match_all('/[\s\S]/u', $value) > $limit) $errors[$field] = 'Máximo ' . $limit . ' caracteres.';
            $values[$field] = $value;
        }
        foreach (['title', 'excerpt', 'content'] as $field) {
            if ($values[$field] === '') $errors[$field] = 'Completa este campo.';
        }
        if (!isset(self::CATEGORIES[$values['category']])) $errors['category'] = 'Elige una categoría.';
        if ($values['source_url'] !== '') {
            $url = parse_url($values['source_url']);
            if (!filter_var($values['source_url'], FILTER_VALIDATE_URL) || !in_array($url['scheme'] ?? '', ['http', 'https'], true) || isset($url['user']) || isset($url['pass'])) {
                $errors['source_url'] = 'Usa una dirección web http o https válida.';
            }
        }
        return [$values, $errors];
    }

    public static function imageData(string $bytes): array
    {
        if ($bytes === '' || strlen($bytes) > 4 * 1024 * 1024) throw new RuntimeException('La foto debe pesar como máximo 4 MB.');
        $dimensions = @getimagesizefromstring($bytes);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        if (!$dimensions || !in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) || $dimensions['mime'] !== $mime
            || $dimensions[0] > 8000 || $dimensions[1] > 8000 || $dimensions[0] * $dimensions[1] > 32000000) {
            throw new RuntimeException('Usa una foto JPG, PNG o WebP de hasta 8000 píxeles por lado y 32 megapíxeles.');
        }
        return ['id' => bin2hex(random_bytes(16)), 'mime' => $mime, 'width' => $dimensions[0], 'height' => $dimensions[1], 'content' => $bytes];
    }

    /** Guarda el texto y la foto juntos; una revisión evita sobrescribir otra pestaña. */
    public function save(array $input, string $status, int $id = 0, int $revision = 0, ?array $image = null, bool $removeImage = false): int
    {
        [$values, $errors] = self::validate($input);
        if ($errors || !in_array($status, ['draft', 'published'], true)) throw new RuntimeException('Revisa los campos antes de guardar.');
        $db = $this->database->connection();
        $db->beginTransaction();
        try {
            $old = null;
            if ($id) {
                $lock = $db->prepare('SELECT * FROM blog_posts WHERE id = ? FOR UPDATE');
                $lock->execute([$id]);
                $old = $lock->fetch();
                if (!$old || (int) $old['revision'] !== $revision) throw new RuntimeException('Esta noticia cambió en otra pestaña. Conserva tu texto y vuelve a abrirla antes de guardar.');
            }
            $imageId = $removeImage ? null : ($old['image_id'] ?? null);
            if ($image) {
                $query = $db->prepare('INSERT INTO blog_images (id, mime, width, height, content, created_at) VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP())');
                $query->execute([$image['id'], $image['mime'], $image['width'], $image['height'], $image['content']]);
                $imageId = $image['id'];
            }
            if ($imageId && $values['image_alt'] === '') throw new RuntimeException('Describe la foto para quienes usan un lector de pantalla.');
            $publishedAt = $old['published_at'] ?? ($status === 'published' ? gmdate('Y-m-d H:i:s') : null);
            $parameters = [$values['title'], $values['excerpt'], $values['content'], $values['category'], $status, $imageId, $values['image_alt'], $values['source_name'], $values['source_url'], $publishedAt];
            if ($old) {
                $query = $db->prepare('UPDATE blog_posts SET title=?, excerpt=?, content=?, category=?, status=?, image_id=?, image_alt=?, source_name=?, source_url=?, published_at=?, revision=revision+1, updated_at=UTC_TIMESTAMP() WHERE id=?');
                $query->execute([...$parameters, $id]);
            } else {
                $slug = strtolower(strtr($values['title'], ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ñ'=>'n']));
                $slug = substr(trim(preg_replace('/[^a-z0-9]+/', '-', $slug), '-'), 0, 150) ?: 'noticia';
                $slug .= '-' . bin2hex(random_bytes(4));
                $query = $db->prepare('INSERT INTO blog_posts (title, excerpt, content, category, status, image_id, image_alt, source_name, source_url, published_at, slug, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())');
                $query->execute([...$parameters, $slug]);
                $id = (int) $db->lastInsertId();
            }
            $db->commit();
            $this->published = null;
            return $id;
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }
    }

    public function image(string $id, bool $private = false): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/D', $id)) return null;
        $query = $this->database->connection()->prepare('SELECT i.* FROM blog_images i WHERE i.id = ?' . ($private ? '' : " AND EXISTS (SELECT 1 FROM blog_posts p WHERE p.image_id = i.id AND p.status = 'published' AND p.published_at <= UTC_TIMESTAMP())"));
        $query->execute([$id]);
        return $query->fetch() ?: null;
    }
}
