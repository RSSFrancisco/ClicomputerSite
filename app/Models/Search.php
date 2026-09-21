<?php
declare(strict_types=1);

namespace App\Models;

final class Search
{
    private array $config;

    public function __construct(private Site $site)
    {
        $this->config = require ROOT_PATH . '/data/search.php';
    }

    /** Convierte acentos y espacios sin exigir extensiones extra en cPanel. */
    private function normalize(string $text): string
    {
        $text = strtolower(strtr($text, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
        ]));
        $text = preg_replace('/\p{M}+/u', '', $text) ?? '';
        $text = preg_replace('/\bwi[\s-]+fi\b/u', 'wifi', $text) ?? '';
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $text) ?? '');
    }

    private function words(string $text): array
    {
        $words = explode(' ', $this->normalize($text));
        return array_values(array_unique(array_filter(array_map(
            fn (string $word): string => $this->config['synonyms'][$word] ?? $word,
            $words
        ))));
    }

    private function flatten(array $values): string
    {
        $parts = [];
        array_walk_recursive($values, static function ($value) use (&$parts): void {
            if (is_string($value)) $parts[] = $value;
        });
        return implode(' ', $parts);
    }

    public function catalog(): array
    {
        $entries = [];
        foreach ($this->site->pages() as $page) {
            $extra = $this->config['pages'][$page['file']] ?? [];
            $content = array_intersect_key($page, array_flip(['service', 'headline', 'intro', 'includes', 'details', 'process', 'faq', 'scope', 'blocks']));
            if ($page['file'] === 'index.html') $content[] = $this->site->settings()['network_services'];
            $entries[] = [
                'url' => $page['file'] === 'index.html' ? '/' : '/' . $page['file'],
                'title' => $page['label'], 'description' => $page['description'],
                'icon' => $page['icon'] ?? $extra['icon'] ?? 'bi-file-earmark-text',
                'category' => isset($page['service']) ? 'Servicio' : (($page['kind'] ?? '') === 'guide' ? 'Guía' : 'Página'),
                'keywords' => $extra['keywords'] ?? '', 'body' => $this->flatten($content),
            ];
            foreach ($page['projects'] ?? [] as $project) {
                $entries[] = [
                    'url' => '/proyectos.html#proyecto-' . $project['id'],
                    'title' => $project['title'], 'description' => $project['description'],
                    'icon' => $project['icon'], 'category' => 'Proyecto',
                    'keywords' => implode(' ', $project['tags']), 'body' => '',
                ];
            }
        }
        foreach ($this->config['sections'] as $section) {
            $entries[] = $section + ['category' => 'Sección', 'body' => ''];
        }
        return $entries;
    }

    public function find(string $query): array
    {
        $queryWords = $this->words($query);
        $terms = array_values(array_diff($queryWords, ['de', 'del', 'el', 'la', 'los', 'las', 'en', 'para', 'y', 'con', 'un', 'una', 'al']));
        if (!$terms) return [];

        $results = [];
        foreach ($this->catalog() as $entry) {
            $score = 0;
            $weights = ['title' => 40, 'keywords' => 20, 'description' => 10, 'body' => 2];
            $fields = [];
            foreach ($weights as $field => $weight) $fields[$field] = $this->words($entry[$field]);
            foreach ($terms as $term) {
                $termScore = 0;
                // Títulos primero, después palabras clave y descripción; el cuerpo pesa menos.
                foreach ($weights as $field => $weight) {
                    foreach ($fields[$field] as $word) {
                        if ($word === $term || (strlen($term) >= 2 && str_starts_with($word, $term))) {
                            $termScore = max($termScore, $weight);
                        }
                    }
                }
                // Exigir todas las palabras evita resultados que solo coinciden con una de ellas.
                if ($termScore === 0) continue 2;
                $score += $termScore;
            }
            if ($fields['title'] === $queryWords) $score += 100;
            $score += ['Servicio' => 5, 'Proyecto' => 3, 'Sección' => 2][$entry['category']] ?? 0;
            $results[] = ['score' => $score, 'item' => array_intersect_key($entry, array_flip(['url', 'title', 'description', 'icon', 'category']))];
        }
        usort($results, static fn (array $a, array $b): int => ($b['score'] <=> $a['score']) ?: strcmp($a['item']['url'], $b['item']['url']));
        return array_column($results, 'item');
    }
}
