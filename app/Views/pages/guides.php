<section class="section-padding-sm"><div class="container">
  <div class="editorial-header"><p class="brand-eyebrow">Antes de cotizar</p>
    <h1>Guías para tomar decisiones sobre tu tecnología</h1>
    <p class="hero-subtitle">Qué preguntar, qué preparar y cómo comparar propuestas de cámaras de seguridad, soporte técnico y páginas web.</p>
  </div>
  <div class="row g-4">
    <?php foreach ($pages as $guide): if (($guide['kind'] ?? '') !== 'guide') continue; ?>
      <div class="col-lg-4"><article class="service-card h-100">
        <h2 class="h4"><a href="/<?= e($guide['file']) ?>"><?= e($guide['headline']) ?></a></h2>
        <p><?= e($guide['intro']) ?></p><a class="service-card-link" href="/<?= e($guide['file']) ?>">Leer: <?= e($guide['label']) ?></a>
      </article></div>
    <?php endforeach ?>
  </div>
</div></section>
