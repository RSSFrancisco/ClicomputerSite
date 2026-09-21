<?php
$servicePages = array_values(array_filter($pages, static fn ($item) => isset($item['service'])));
$priority = ['seguridad.html' => 0, 'soporte-tecnico.html' => 1, 'desarrollo-web.html' => 2];
usort($servicePages, static fn ($a, $b) => ($priority[$a['file']] ?? 3) <=> ($priority[$b['file']] ?? 3));
?>
<section class="services-section section-padding" id="servicios"><div class="container">
  <div class="section-header"><span class="section-badge">Nuestros servicios</span>
    <h2>Cámaras, computadoras y <span class="text-gradient">presencia en línea</span></h2>
    <p>Elige el servicio que necesitas en Córdoba, Veracruz. Revisa su alcance y la información necesaria para cotizar.</p>
  </div>
  <div class="row g-4 stagger-children">
    <?php foreach ($servicePages as $service): ?>
      <div class="col-lg-4 col-md-6"><article class="service-card h-100">
        <div class="service-card-icon"><i class="bi <?= e($service['icon'] ?? 'bi-camera-video') ?>" aria-hidden="true"></i></div>
        <h3 class="h4"><?= e($service['label']) ?></h3><p><?= e($service['description']) ?></p>
        <a class="service-card-link" href="/<?= e($service['file']) ?>">Ver <?= e($service['label']) ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
      </article></div>
    <?php endforeach ?>
  </div>
</div></section>
