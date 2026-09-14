<?php
$hasGlobalNetwork = in_array('home', $page['sections'] ?? [], true);
$hasSecurityNetwork = in_array('seguridad', $page['sections'] ?? [], true);
?>
<!DOCTYPE html>
<html lang="es-MX" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?= $view->render('partials/seo', compact('page', 'site', 'canonical', 'schema')) ?>
  <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;family=JetBrains+Mono:wght@400;500&amp;display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <?php foreach (['variables', 'base', 'components', 'sections', 'responsive'] as $style): ?>
    <link rel="stylesheet" href="<?= e(asset('css/' . $style . '.css')) ?>">
  <?php endforeach ?>
  <?php if ($hasGlobalNetwork): ?>
    <link rel="stylesheet" href="<?= e(asset('css/global-network.css')) ?>">
  <?php endif ?>
  <?php if ($hasSecurityNetwork): ?>
    <link rel="stylesheet" href="<?= e(asset('css/security-network.css')) ?>">
  <?php endif ?>
  <script>
    try {
      var theme = localStorage.getItem('cli-theme');
      document.documentElement.setAttribute('data-bs-theme', theme === 'light' || theme === 'dark' ? theme :
        (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark'));
    } catch (_) { /* El contenido funciona también sin almacenamiento local. */ }
  </script>
</head>
<body>
  <a class="skip-link" href="#contenido">Saltar al contenido</a>
  <?= $view->render('partials/navbar', compact('page')) ?>
  <main id="contenido">
    <?php if ($page['file'] !== 'index.html'): ?>
      <?= $view->render('partials/breadcrumb', compact('page')) ?>
    <?php endif ?>
    <?= $content ?>
  </main>
  <?= $view->render('partials/footer', compact('site')) ?>
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <?php if ($hasGlobalNetwork || $hasSecurityNetwork): ?>
    <script defer src="<?= e(asset('js/vendor/jquery-4.0.0.slim.min.js')) ?>"></script>
    <script defer src="<?= e(asset('js/components/network-animation.js')) ?>"></script>
  <?php endif ?>
  <?php foreach (['components/theme-switcher', 'components/navbar', 'components/animations', 'views/contact-form', 'components/mouse-follower', 'components/search', 'app'] as $script): ?>
    <script defer src="<?= e(asset('js/' . $script . '.js')) ?>"></script>
  <?php endforeach ?>
</body>
</html>
