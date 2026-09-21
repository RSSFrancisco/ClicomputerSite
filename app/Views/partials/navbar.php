<?php
$links = ['/#inicio' => 'Inicio', '/#servicios' => 'Servicios',
    '/seguridad.html' => 'Cámaras', '/proyectos.html' => 'Proyectos', '/guias.html' => 'Guías', '/contacto.html' => 'Contacto'];
$active = match ($page['file']) {
    'index.html' => '/#inicio', 'seguridad.html' => '/seguridad.html', 'proyectos.html' => '/proyectos.html',
    'contacto.html' => '/contacto.html', 'guias.html' => '/guias.html',
    default => isset($page['service']) ? '/#servicios' : '/' . ($page['parent']['file'] ?? ''),
};
?>
<nav class="navbar navbar-expand-xl cli-navbar" id="mainNavbar" aria-label="Navegación principal">
  <div class="container">
    <a class="navbar-brand" href="/" aria-label="Clicomputer"><?= $view->render('partials/brand') ?></a>
    <div class="d-flex align-items-center gap-2 d-xl-none">
      <button type="button" class="theme-toggle" hidden id="themeToggleMobile" aria-label="Cambiar tema"><i class="bi bi-sun" aria-hidden="true"></i></button>
      <button type="button" class="navbar-toggler" hidden data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Menú">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
    <div class="navbar-collapse" id="navbarMain">
      <ul class="navbar-nav mx-auto mb-2 mb-xl-0">
        <?php foreach ($links as $href => $label): ?>
          <li class="nav-item"><a class="nav-link<?= $active === $href ? ' active' : '' ?>" href="<?= e($href) ?>"><?= e($label) ?></a></li>
        <?php endforeach ?>
      </ul>
      <?= $view->render('partials/site-search', ['searchQuery' => $searchQuery ?? '']) ?>
      <div class="d-none d-xl-flex align-items-center gap-3">
        <button type="button" class="theme-toggle" hidden id="themeToggle" aria-label="Cambiar tema"><i class="bi bi-sun" aria-hidden="true"></i></button>
        <a href="/#contacto" class="btn btn-cta-nav">Cotizar</a>
      </div>
      <div class="d-xl-none mt-2"><a href="/#contacto" class="btn btn-cta-nav w-100">Cotizar ahora</a></div>
    </div>
  </div>
</nav>
