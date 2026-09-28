<?php
$isCv = $page['file'] === 'cv.html';
$isNews = ($page['kind'] ?? '') === 'news';
$image = $site['url'] . ($isCv ? '/assets/img/profile.jpeg' : '/assets/img/social-card.png');
$imageAlt = $isCv ? 'Francisco Reyes Sánchez' : 'Clicomputer México: software, páginas web, soporte, redes y cámaras de seguridad en Córdoba, Veracruz.';
if ($isNews && $page['post']['image_id']) {
    $image = $site['url'] . '/noticias/imagen/' . $page['post']['image_id'];
    $imageAlt = $page['post']['image_alt'];
}
?>
<title><?= e($page['title']) ?></title>
<meta name="description" content="<?= e($page['description']) ?>">
<meta name="author" content="<?= e($isCv ? 'Francisco Reyes Sánchez' : $site['name']) ?>">
<meta name="robots" content="<?= e($page['robots'] ?? 'index, follow, max-image-preview:large') ?>">
<?php if ($page['file'] !== '404.html'): ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<?php endif ?>
<meta property="og:title" content="<?= e($page['title']) ?>">
<meta property="og:description" content="<?= e($page['description']) ?>">
<meta property="og:type" content="<?= $isCv ? 'profile' : ($isNews ? 'article' : 'website') ?>">
<meta property="og:locale" content="es_MX">
<meta property="og:site_name" content="<?= e($site['name']) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($image) ?>">
<meta property="og:image:alt" content="<?= e($imageAlt) ?>">
<?php if (!$isCv && !($isNews && $page['post']['image_id'])): ?>
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<?php endif ?>
<?php if ($isNews): ?>
<meta property="article:published_time" content="<?= e(str_replace(' ', 'T', $page['post']['published_at']) . 'Z') ?>">
<meta property="article:modified_time" content="<?= e(str_replace(' ', 'T', $page['post']['updated_at']) . 'Z') ?>">
<?php endif ?>
<meta name="twitter:card" content="<?= $isCv ? 'summary' : 'summary_large_image' ?>">
<meta name="twitter:title" content="<?= e($page['title']) ?>">
<meta name="twitter:description" content="<?= e($page['description']) ?>">
<meta name="twitter:image" content="<?= e($image) ?>">
<?php if ($page['file'] !== '404.html'): ?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
<?php endif ?>
