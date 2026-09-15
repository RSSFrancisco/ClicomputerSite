<?php
declare(strict_types=1);

// Complementa las páginas existentes con destinos concretos y palabras habituales.
return [
    'pages' => [
        'index.html' => ['icon' => 'bi-house', 'keywords' => 'inicio clicomputer tecnología'],
        'seguridad.html' => ['icon' => 'bi-camera-video', 'keywords' => 'cctv cámaras videovigilancia dahua hikvision grabación nvr dvr'],
        'proyectos.html' => ['icon' => 'bi-folder2-open', 'keywords' => 'portafolio trabajos proyectos'],
        'cv.html' => ['icon' => 'bi-person', 'keywords' => 'curriculum cv francisco reyes experiencia'],
    ],
    'sections' => [
        ['url' => '/#servicios', 'title' => 'Nuestros servicios', 'icon' => 'bi-grid',
            'description' => 'Desarrollo web y software, soporte técnico, redes y cámaras de seguridad.',
            'keywords' => 'servicios soluciones tecnología'],
        ['url' => '/#nosotros', 'title' => 'Nosotros', 'icon' => 'bi-people',
            'description' => 'Conoce a Clicomputer, nuestra experiencia y nuestra forma de trabajar.',
            'keywords' => 'empresa equipo misión visión quienes somos'],
        ['url' => '/#contacto', 'title' => 'Contacto y cotizaciones', 'icon' => 'bi-chat-dots',
            'description' => 'Cuéntanos tu proyecto, solicita una cotización o consulta nuestros datos de contacto.',
            'keywords' => 'contacto cotizar cotización presupuesto precio teléfono whatsapp correo horario'],
        ['url' => '/seguridad.html#industrialPhotos', 'title' => 'Instalaciones industriales', 'icon' => 'bi-building',
            'description' => 'Fotografías de cámaras, montaje y monitoreo en un entorno industrial.',
            'keywords' => 'instalaciones industriales fotografías fotos galería montaje bodega nave'],
    ],
    // Se normalizan también los documentos: "wifi" y "redes" comparten una palabra.
    'synonyms' => [
        'cctv' => 'camara', 'camaras' => 'camara', 'videovigilancia' => 'camara', 'vigilancia' => 'camara',
        'wifi' => 'red', 'redes' => 'red', 'lan' => 'red', 'wlan' => 'red',
        'pc' => 'soporte', 'pcs' => 'soporte', 'reparacion' => 'soporte', 'reparaciones' => 'soporte',
        'computadora' => 'soporte', 'computadoras' => 'soporte', 'laptop' => 'soporte', 'laptops' => 'soporte',
        'instalaciones' => 'instalacion', 'industriales' => 'industrial',
        'paginas' => 'pagina', 'servicios' => 'servicio', 'proyectos' => 'proyecto',
        'fotos' => 'foto', 'fotografias' => 'foto', 'fotografia' => 'foto',
    ],
];
