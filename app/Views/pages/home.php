<section class="hero-section brand-hero" id="inicio">
      <div class="bg-gradient-radial"></div>
      <div class="bg-dots-pattern"></div>
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <div class="hero-content">
              <p class="brand-eyebrow">Clicomputer <span aria-hidden="true">/</span> <?= e($site['locality'] . ', ' . $site['region']) ?></p>
              <h1 class="hero-title">Páginas web, software y <span class="text-brand">soporte en Córdoba.</span>
              </h1>
              <p class="hero-subtitle">Desde tu página web hasta la red y las cámaras de tu negocio. Te ayudamos a elegir,
                instalar y mantener la tecnología que necesitas.</p>
              <div class="hero-actions fade-in-up">
                <a href="/#contacto" class="btn btn-primary-custom">Cuéntanos qué necesitas <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                <a href="/#servicios" class="btn btn-outline-custom">Ver servicios</a>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <?= $view->render('partials/global-network', ['services' => $site['network_services']]) ?>
          </div>
        </div>
      </div>
    </section>
    <div class="tech-strip">
      <div class="container">
        <p class="tech-strip-label">Tecnologías que dominamos</p>
        <div class="tech-strip-logos">
          <i class="bi bi-filetype-html" title="HTML5"></i><i class="bi bi-filetype-css" title="CSS3"></i><i
            class="bi bi-filetype-js" title="JavaScript"></i>
          <i class="bi bi-filetype-php" title="PHP"></i><i class="bi bi-database" title="Bases de Datos"></i><i
            class="bi bi-git" title="Git"></i>
          <i class="bi bi-bootstrap" title="Bootstrap"></i><i class="bi bi-windows" title="Windows"></i><i
            class="bi bi-hdd-network" title="Networking"></i>
          <i class="bi bi-camera-video" title="CCTV"></i>
        </div>
      </div>
    </div>
