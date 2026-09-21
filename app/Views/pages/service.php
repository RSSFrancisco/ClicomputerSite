<section class="service-hero section-padding-sm" id="servicio">
  <div class="container"><div class="service-intro">
    <span class="section-badge"><i class="bi <?= e($page['icon']) ?>" aria-hidden="true"></i> <?= e($page['label']) ?></span>
    <h1><?= e($page['headline']) ?></h1>
    <p class="hero-subtitle"><?= e($page['intro']) ?></p>
    <div class="hero-actions"><a class="btn btn-primary-custom" href="/#contacto">Solicitar cotización</a>
      <a class="btn btn-outline-custom" href="<?= e('https://wa.me/' . ltrim($site['telephone'], '+')) ?>">Consultar por WhatsApp</a></div>
  </div></div>
</section>
<section class="section-padding-sm"><div class="container">
  <h2 class="mb-4">Cómo podemos ayudarte</h2>
  <div class="row g-4">
    <?php foreach ($page['includes'] as [$title, $body]): ?>
      <div class="col-lg-4"><article class="service-card h-100"><h3 class="h4"><?= e($title) ?></h3><p><?= e($body) ?></p></article></div>
    <?php endforeach ?>
  </div>
  <p class="service-details"><?= e($page['details']) ?></p>
</div></section>
<?= $view->render('partials/service-scope', compact('page')) ?>
<section class="section-padding-sm service-process"><div class="container"><div class="row g-5">
  <div class="col-lg-6"><h2>Cómo iniciamos tu proyecto</h2><ol class="process-list">
    <?php foreach ($page['process'] as $step): ?><li><?= e($step) ?></li><?php endforeach ?>
  </ol></div>
  <div class="col-lg-6"><h2>Atención en Córdoba, Veracruz</h2>
    <p>Cuéntanos dónde necesitas el servicio y qué buscas resolver. Confirmaremos contigo la modalidad y disponibilidad de atención antes de cotizar.</p>
    <p><a href="/proyectos.html">Consulta nuestro portafolio de proyectos</a> para conocer otros trabajos de Clicomputer.</p>
    <a href="tel:<?= e($site['telephone']) ?>">Llámanos al <?= e($site['telephone_display']) ?></a>
  </div>
</div></div></section>
<section class="section-padding-sm"><div class="container service-faq">
  <h2 class="mb-4">Preguntas frecuentes</h2>
  <?php foreach ($page['faq'] as [$question, $answer]): ?>
    <details><summary><?= e($question) ?></summary><p><?= e($answer) ?></p></details>
  <?php endforeach ?>
</div></section>
<section class="section-padding-sm"><div class="container">
  <h2>Servicios relacionados</h2><ul class="related-services">
    <?php foreach ($pages as $other): ?>
      <?php if (isset($other['service']) && $other['file'] !== $page['file']): ?>
        <li><a href="/<?= e($other['file']) ?>"><?= e($other['label']) ?></a></li>
      <?php endif ?>
    <?php endforeach ?>
  </ul>
</div></section>
