<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page['title']) ?></title>
  <meta name="description" content="<?= e($page['description']) ?>">
  <meta name="robots" content="noindex, nofollow">
  <meta name="referrer" content="no-referrer">
  <meta name="theme-color" content="#701e30">
  <link rel="canonical" href="<?= e($canonical) ?>">
  <meta property="og:title" content="<?= e($page['title']) ?>">
  <meta property="og:description" content="<?= e($page['description']) ?>">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="es_MX">
  <meta property="og:url" content="<?= e($canonical) ?>">
  <?php if ($event): ?>
    <meta property="og:image" content="<?= e($canonical . '/invitacion.png') ?>">
    <meta property="og:image:alt" content="Invitación de boda de Anselmo y Dulce">
    <meta name="twitter:card" content="summary_large_image">
  <?php endif ?>
  <link rel="icon" type="image/svg+xml" href="<?= e(asset('assets/img/wedding-monogram.svg')) ?>">
  <link rel="preload" href="/assets/fonts/inter-latin.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?= e(asset('css/fonts.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/vendor/fontawesome/css/fontawesome.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/vendor/fontawesome/css/solid.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/events.css')) ?>">
  <script defer src="<?= e(asset('js/components/events.js')) ?>"></script>
  <?php if ($event && !empty($event['music'])): ?>
    <script defer src="<?= e(asset('js/components/wedding-music.js')) ?>"></script>
  <?php endif ?>
</head>
<body class="event-page<?= $event ? ' wedding-page' : '' ?>">
  <a class="event-skip" href="#contenido">Saltar al contenido</a>
  <?= $content ?>
</body>
</html>
