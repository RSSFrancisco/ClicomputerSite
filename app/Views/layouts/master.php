<?php
$hasGlobalNetwork = in_array('home', $page['sections'] ?? [], true);
$hasSecurityNetwork = in_array('seguridad', $page['sections'] ?? [], true);
$hasSecurityGallery = isset($page['gallery']);
?>
<!DOCTYPE html>
<html lang="es-MX" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?= $view->render('partials/seo', compact('page', 'site', 'canonical', 'schema')) ?>
  <link rel="icon" type="image/png" href="<?= e(asset('assets/img/clicomputer-symbol.png')) ?>">
  <link rel="preload" href="/assets/fonts/inter-latin.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/vendor/bootstrap.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/vendor/bootstrap-icons.min.css')) ?>">
  <?php foreach (['variables', 'base', 'components', 'sections', 'responsive'] as $style): ?>
    <link rel="stylesheet" href="<?= e(asset('css/' . $style . '.css')) ?>">
  <?php endforeach ?>
  <?php if ($hasGlobalNetwork): ?>
    <link rel="stylesheet" href="<?= e(asset('css/global-network.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/space-journey.css')) ?>">
  <?php endif ?>
  <?php if ($hasSecurityNetwork): ?>
    <link rel="stylesheet" href="<?= e(asset('css/security-network.css')) ?>">
  <?php endif ?>
  <?php if ($hasSecurityGallery): ?>
    <link rel="stylesheet" href="<?= e(asset('css/security-gallery.css')) ?>">
  <?php endif ?>
  <link rel="stylesheet" href="<?= e(asset('css/brand.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/search.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/editorial.css')) ?>">
  <script>
    document.documentElement.classList.add('js');
    try {
      var theme = localStorage.getItem('cli-theme');
      document.documentElement.setAttribute('data-bs-theme', theme === 'light' || theme === 'dark' ? theme :
        (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark'));
    } catch (_) { /* El contenido funciona también sin almacenamiento local. */ }
  </script>
</head>
<body>
  <a class="skip-link" href="#contenido">Saltar al contenido</a>
  <?= $view->render('partials/navbar', ['page' => $page, 'searchQuery' => $searchQuery ?? '']) ?>
  <main id="contenido"<?= $hasGlobalNetwork ? ' class="has-space-journey"' : '' ?>>
    <?php if ($hasGlobalNetwork): ?>
      <?= $view->render('partials/space-journey') ?>
    <?php endif ?>
    <?php if ($page['file'] !== 'index.html'): ?>
      <?= $view->render('partials/breadcrumb', compact('page')) ?>
    <?php endif ?>
    <?= $content ?>
  </main>
  <?= $view->render('partials/footer', compact('site')) ?>
  <?= $view->render('partials/analytics', compact('site', 'page', 'canonical')) ?>
  <script defer src="<?= e(asset('js/vendor/bootstrap.bundle.min.js')) ?>"></script>
  <script defer src="<?= e(asset('js/vendor/jquery-4.0.0.slim.min.js')) ?>"></script>
  <?php if ($hasGlobalNetwork || $hasSecurityNetwork): ?>
    <script defer src="<?= e(asset('js/components/network-animation.js')) ?>"></script>
  <?php endif ?>
  <?php if ($hasGlobalNetwork): ?>
    <script defer src="<?= e(asset('js/components/service-popovers.js')) ?>"></script>
    <script defer src="<?= e(asset('js/components/space-journey.js')) ?>"></script>
  <?php endif ?>
  <?php if ($hasSecurityGallery): ?>
    <script defer src="<?= e(asset('js/components/security-gallery.js')) ?>"></script>
  <?php endif ?>
  <?php foreach (['components/theme-switcher', 'components/navbar', 'components/animations', 'views/contact-form', 'components/search', 'app'] as $script): ?>
    <script defer src="<?= e(asset('js/' . $script . '.js')) ?>"></script>
  <?php endforeach ?>
</body>
</html>
