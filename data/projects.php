<?php
declare(strict_types=1);

// Una sola fuente para las tarjetas del portafolio y los resultados de búsqueda.
return [
    ['id' => 'inventarios', 'category' => 'software', 'title' => 'Sistema de Inventarios',
        'description' => 'Aplicación de gestión de inventarios con control de entradas, salidas y reportes en tiempo real.',
        'tags' => ['C#', 'SQL Server', '.NET'], 'icon' => 'bi-kanban', 'color' => 'var(--brand-primary)',
        'background' => 'linear-gradient(135deg, rgba(75,163,217,0.2), rgba(63,185,80,0.2))'],
    ['id' => 'ecommerce', 'category' => 'web', 'title' => 'E-Commerce Corporativo',
        'description' => 'Tienda en línea completa con carrito, pasarela de pagos y panel de administración.',
        'tags' => ['PHP', 'MySQL', 'Bootstrap'], 'icon' => 'bi-shop', 'color' => 'var(--accent-purple)',
        'background' => 'linear-gradient(135deg, rgba(88,196,240,0.2), rgba(188,140,255,0.2))'],
    ['id' => 'red-corporativa', 'category' => 'redes', 'title' => 'Infraestructura de Red Corporativa',
        'description' => 'Diseño e implementación de red LAN para oficinas con 50+ usuarios y cobertura Wi-Fi empresarial.',
        'tags' => ['MikroTik', 'Ubiquiti', 'Cat6'], 'icon' => 'bi-hdd-rack', 'color' => 'var(--accent-cyan)',
        'background' => 'linear-gradient(135deg, rgba(57,210,192,0.2), rgba(75,163,217,0.2))'],
    ['id' => 'cctv-industrial', 'category' => 'seguridad', 'title' => 'CCTV Planta Industrial',
        'description' => 'Sistema de 32 cámaras IP con NVR, monitoreo remoto 24/7 y almacenamiento en la nube.',
        'tags' => ['Hikvision', 'NVR', 'PoE'], 'icon' => 'bi-camera-video', 'color' => 'var(--accent-orange)',
        'background' => 'linear-gradient(135deg, rgba(248,81,73,0.2), rgba(240,136,62,0.2))'],
    ['id' => 'energia-solar', 'category' => 'web', 'title' => 'Landing Page Energía Solar',
        'description' => 'Página web optimizada para conversión de leads en el sector de energía renovable.',
        'tags' => ['HTML5', 'CSS3', 'JavaScript'], 'icon' => 'bi-laptop', 'color' => 'var(--accent-blue)',
        'background' => 'linear-gradient(135deg, rgba(75,163,217,0.2), rgba(88,166,255,0.2))'],
    ['id' => 'facturacion', 'category' => 'software', 'title' => 'Sistema de Facturación',
        'description' => 'Sistema de facturación electrónica CFDI integrado con SAT para PyMEs mexicanas.',
        'tags' => ['PHP', 'MySQL', 'API SAT'], 'icon' => 'bi-receipt', 'color' => 'var(--accent-green)',
        'background' => 'linear-gradient(135deg, rgba(63,185,80,0.2), rgba(75,163,217,0.2))'],
    ['id' => 'ceopi', 'category' => 'web', 'title' => 'Página Web CEOPI',
        'description' => 'Diseño y desarrollo de página web institucional para la empresa ceopi.com.',
        'tags' => ['HTML5', 'CSS3', 'JavaScript'], 'icon' => 'bi-globe', 'color' => 'var(--accent-purple)',
        'background' => 'linear-gradient(135deg, rgba(188,140,255,0.2), rgba(75,163,217,0.2))', 'website' => 'https://ceopi.com'],
];
