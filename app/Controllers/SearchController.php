<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\Search;

final class SearchController
{
    public function __construct(private Search $search, private PageController $pages) {}

    public function show(array $params): Response
    {
        $query = $params['q'] ?? '';
        $error = null;
        // Rechaza arrays y bytes inválidos antes de normalizar o reflejar la consulta.
        if (!is_string($query) || !preg_match('//u', $query)) {
            $query = '';
            $error = 'Escribe una búsqueda de texto válida.';
        }
        $query = trim(preg_replace('/\s+/u', ' ', $query) ?? '');
        preg_match_all('/./us', $query, $characters);
        $length = count($characters[0]);
        if ($length > 120) {
            $query = implode('', array_slice($characters[0], 0, 120));
            $error = 'La búsqueda puede tener hasta 120 caracteres.';
        } elseif ($length === 1) {
            $error = 'Escribe al menos dos caracteres para buscar.';
        }
        $results = $query !== '' && !$error ? $this->search->find($query) : [];
        $status = $error ? 400 : 200;
        $headers = ['Cache-Control' => 'no-store', 'X-Robots-Tag' => 'noindex, follow'];

        // El mismo motor responde al formulario HTML y a las sugerencias: no se duplican reglas.
        if (($params['format'] ?? '') === 'json') {
            return new Response(json_encode([
                'query' => $query, 'total' => count($results), 'results' => array_slice($results, 0, 5), 'error' => $error,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), $status,
                ['Content-Type' => 'application/json; charset=UTF-8'] + $headers);
        }
        $response = $this->pages->render([
            'file' => 'buscar', 'label' => 'Buscar', 'title' => 'Buscar en Clicomputer',
            'description' => 'Encuentra servicios, proyectos, instalaciones y formas de contacto en Clicomputer.',
            'robots' => 'noindex, follow', 'sections' => ['search'],
        ], ['searchQuery' => $query, 'searchResults' => $results, 'searchError' => $error], $status);
        $response->headers += $headers;
        return $response;
    }
}
