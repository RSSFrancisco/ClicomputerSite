<?php
declare(strict_types=1);

// Las invitaciones se resuelven aparte del catálogo público, el buscador y el sitemap.
return [
    'anselmo-y-dulce' => [
        'slug' => 'anselmo-y-dulce',
        'names' => ['Anselmo', 'Dulce'],
        'title' => 'Anselmo & Dulce · Nuestra boda',
        'description' => 'Con la bendición de Dios, te invitamos a nuestra boda. Sábado 28 de noviembre de 2026. Misa a las 4:00 p. m. y recepción a las 5:30 p. m.',
        'date' => '2026-11-28',
        'date_display' => '28 de noviembre de 2026',
        'start' => '2026-11-28T16:00:00-06:00',
        'music' => ['file' => 'symphony.mp3', 'label' => 'Symphony', 'artist' => 'Clean Bandit · Zara Larsson'],
        'ceremony' => [
            'title' => 'Ceremonia religiosa',
            'venue' => 'Parroquia San Rafael Guizar y Valencia',
            'address' => 'Santa Leticia',
            'time' => '4:00 p. m.',
            'start' => '2026-11-28T16:00:00-06:00',
        ],
        'reception' => [
            'title' => 'La celebración',
            'venue' => 'Salón el Campesino',
            'address' => 'La Luz, Francisco I. Madero',
            'reference' => 'Junto al Campo Deportivo',
            'time' => '5:30 p. m.',
            'start' => '2026-11-28T17:30:00-06:00',
        ],
    ],
];
