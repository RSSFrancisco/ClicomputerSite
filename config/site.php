<?php
declare(strict_types=1);

return array (
  'name' => 'Clicomputer México',
  'url' => 'https://www.clcomputer.com',
  'telephone' => '+526567514187',
  'telephone_display' => '+52 656 751 4187',
  'email' => 'info@clcomputer.com',
  'locality' => 'Córdoba',
  'region' => 'Veracruz',
  'country' => 'MX',
  'hours_display' => 'Lunes a sábado: 10:00 a 19:00',
  'hours_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
  'hours_open' => '10:00',
  'hours_close' => '19:00',
  'service_mode' => 'Atención a domicilio y en línea',
  // Propiedad confirmada en Analytics desde el perfil del propietario.
  // CLICOMPUTER_GA4_ID=disabled permite suspender la integración.
  'analytics_id' => trim((string) (getenv('CLICOMPUTER_GA4_ID') ?: 'G-C5KG6QTZJP')),
  'network_services' => [
    [
      'id' => 'seguridad', 'label' => 'Seguridad', 'icon' => 'bi-shield-lock',
      'description' => 'Cámaras de seguridad, grabación y monitoreo remoto para proteger tu hogar o negocio.',
    ],
    [
      'id' => 'soporte', 'label' => 'Soporte', 'icon' => 'bi-tools',
      'description' => 'Diagnóstico, mantenimiento y reparación de equipos de cómputo para mantener tu operación en marcha.',
    ],
    [
      'id' => 'redes', 'label' => 'Redes', 'icon' => 'bi-diagram-3',
      'description' => 'Redes empresariales, cableado estructurado y Wi-Fi para conectar tus equipos de forma estable y segura.',
    ],
    [
      'id' => 'marketing', 'label' => 'Marketing', 'icon' => 'bi-megaphone',
      'description' => 'Estrategia digital, contenido y presencia en línea para dar a conocer tu negocio y conectar con tus clientes.',
    ],
    [
      'id' => 'software', 'label' => 'Software', 'icon' => 'bi-code-slash',
      'description' => 'Sistemas a la medida para organizar información, automatizar procesos y facilitar la operación de tu empresa.',
    ],
  ],
  'pages' => 
  array (
    0 => 
    array (
      'file' => 'index.html',
      'title' => 'Cámaras, soporte y páginas web en Córdoba | Clicomputer',
      'description' => 'Instalación de cámaras de seguridad, soporte técnico de computadoras y creación de páginas web en Córdoba, Veracruz. Consulta servicios y solicita tu cotización.',
      'label' => 'Inicio',
      'sections' => 
      array (
        0 => 'home',
        1 => 'services',
        2 => 'about',
        3 => 'contact',
      ),
    ),
    1 => 
    array (
      'file' => 'seguridad.html',
      'title' => 'Cámaras de seguridad en Córdoba, Veracruz | Clicomputer',
      'description' => 'Instalación de cámaras de seguridad y CCTV en Córdoba, Veracruz. Sistemas IP, grabación y acceso remoto para hogares y negocios. Cotiza tu instalación.',
      'label' => 'Cámaras de seguridad',
      'gallery' => require ROOT_PATH . '/data/security-gallery.php',
      'service' => 'Instalación de cámaras de seguridad y CCTV',
      'sections' => 
      array (
        0 => 'seguridad',
      ),
    ),
    2 => 
    array (
      'file' => 'proyectos.html',
      'title' => 'Proyectos de software, web y redes | Clicomputer México',
      'description' => 'Conoce los proyectos de Clicomputer: sistemas de inventarios, sitios web, redes empresariales y videovigilancia. Encuentra ideas para tu próximo proyecto.',
      'label' => 'Proyectos',
      'projects' => require ROOT_PATH . '/data/projects.php',
      'sections' => 
      array (
        0 => 'projects',
      ),
    ),
  ),
);
