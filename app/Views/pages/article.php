<article class="editorial-page section-padding-sm"><div class="container">
  <header class="editorial-header">
    <p class="brand-eyebrow"><?= ($page['kind'] ?? '') === 'guide' ? 'Guías para tu negocio' : 'Portafolio de Clicomputer' ?></p>
    <h1><?= e($page['headline']) ?></h1>
    <p class="hero-subtitle"><?= e($page['intro']) ?></p>
    <?php if ($page['kind'] === 'guide'): ?><p class="editorial-byline">Por <?= e($site['name']) ?> · <?= e($site['locality'] . ', ' . $site['region']) ?></p><?php endif ?>
  </header>
  <div class="editorial-body">
    <?php foreach ($page['blocks'] as $block): ?>
    <section class="editorial-section"><h2><?= e($block['title']) ?></h2>
      <?php foreach ($block['paragraphs'] ?? [] as $paragraph): ?><p><?= e($paragraph) ?></p><?php endforeach ?>
      <?php if (isset($block['items'])): ?><ul class="content-checklist"><?php foreach ($block['items'] as $item): ?><li><?= e($item) ?></li><?php endforeach ?></ul><?php endif ?>
    </section>
    <?php endforeach ?>
    <?php if (isset($page['website'])): ?><p><a class="btn btn-outline-custom" href="<?= e($page['website']) ?>" target="_blank" rel="noopener noreferrer">Visitar el sitio de CEOPI</a></p><?php endif ?>
  </div>
</div>
<?php if (isset($page['gallery'])): ?><?= $view->render('partials/security-gallery', ['photos' => $page['gallery']]) ?><?php endif ?>
<div class="container"><aside class="editorial-related" aria-label="Servicios y lecturas relacionadas">
  <h2>Continúa con tu proyecto</h2><div class="content-links">
    <?php foreach ($page['related'] as $related): ?><a href="/<?= e($related['file']) ?>"><?= e($related['label']) ?></a><?php endforeach ?>
  </div>
  <p class="mt-4">Cuéntanos qué necesitas y dónde requieres el servicio. Confirmaremos contigo el alcance y la disponibilidad.</p>
  <a href="/contacto.html" class="btn btn-primary-custom">Solicitar una cotización</a>
</aside></div></article>
