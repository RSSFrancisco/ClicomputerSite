<!-- Clicomputer México — Projects Section -->
<section class="projects-section section-padding" id="proyectos">
  <div class="container">
    <!-- Header -->
    <div class="section-header">
      <span class="section-badge fade-in-up">
        <i class="bi bi-folder2-open"></i> Portafolio
      </span>
      <h1 class="fade-in-up">Proyectos <span class="text-gradient">destacados</span></h1>
      <p class="fade-in-up">Una muestra de los proyectos que hemos realizado para nuestros clientes en diversas industrias.</p>
    </div>

    <!-- Filter Buttons -->
    <div class="project-filters fade-in-up" hidden>
      <button class="filter-btn active" data-filter="all">Todos</button>
      <button class="filter-btn" data-filter="software">Software</button>
      <button class="filter-btn" data-filter="web">Web</button>
      <button class="filter-btn" data-filter="redes">Redes</button>
      <button class="filter-btn" data-filter="seguridad">Seguridad</button>
    </div>

    <!-- Los identificadores permiten enlazar directamente desde el buscador. -->
    <div class="row g-4 stagger-children">
      <?php foreach ($page['projects'] as $project): ?>
        <div class="col-lg-4 col-md-6">
          <div class="project-card fade-in-up" data-category="<?= e($project['category']) ?>" id="proyecto-<?= e($project['id']) ?>">
            <div class="project-card-img">
              <div style="width:100%;height:100%;background:<?= e($project['background']) ?>;display:flex;align-items:center;justify-content:center;">
                <i class="bi <?= e($project['icon']) ?>" style="font-size:3rem;color:<?= e($project['color']) ?>"></i>
              </div>
              <div class="project-card-overlay">
                <?php if (isset($project['website'])): ?>
                  <a href="<?= e($project['website']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary-custom btn-sm">Visitar Web</a>
                <?php else: ?>
                  <a href="/#contacto" class="btn btn-primary-custom btn-sm">Consultar un proyecto similar</a>
                <?php endif ?>
              </div>
            </div>
            <div class="project-card-body">
              <h2 class="h5"><?= e($project['title']) ?></h2>
              <p><?= e($project['description']) ?></p>
              <div class="d-flex flex-wrap gap-1">
                <?php foreach ($project['tags'] as $tag): ?><span class="tech-tag"><?= e($tag) ?></span><?php endforeach ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="cta-section section-padding-sm">
  <div class="container">
    <div class="cta-card fade-in-up">
      <h2>¿Listo para impulsar tu negocio?</h2>
      <p>Contáctanos hoy y recibe una cotización personalizada sin compromiso para tu proyecto.</p>
      <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="/#contacto" class="btn btn-primary-custom">
          <i class="bi bi-chat-dots"></i> Solicitar Cotización
        </a>
        <a href="<?= e('https://wa.me/' . ltrim($site['telephone'], '+')) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-custom">
          <i class="bi bi-whatsapp"></i> WhatsApp
        </a>
      </div>
    </div>
  </div>
</section>
