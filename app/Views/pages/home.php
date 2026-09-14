<section class="hero-section" id="inicio">
      <div class="bg-gradient-radial"></div>
      <div class="bg-dots-pattern"></div>
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <div class="hero-content">
              <div class="section-badge fade-in-up"><i class="bi bi-rocket-takeoff"></i> Soluciones Tecnológicas
                Integrales</div>
              <h1 class="hero-title fade-in-up">Páginas web, software y <span class="text-gradient">soporte en Córdoba</span>
              </h1>
              <p class="hero-subtitle fade-in-up">Desarrollo de software a la medida, páginas web profesionales, soporte
                técnico, infraestructura de redes y sistemas de videovigilancia para tu empresa.</p>
              <div class="hero-actions fade-in-up">
                <a href="/#servicios" class="btn btn-primary-custom"><i class="bi bi-grid-3x3-gap"></i> Ver Servicios</a>
                <a href="/#contacto" class="btn btn-outline-custom"><i class="bi bi-chat-dots"></i> Contáctanos</a>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <?= $view->render('partials/global-network', ['services' => $site['network_services']]) ?>
            <div class="hero-badges">
              <span class="hero-float-badge"><i class="bi bi-shield-check text-brand" aria-hidden="true"></i> Seguridad Garantizada</span>
              <span class="hero-float-badge"><i class="bi bi-lightning-charge text-brand" aria-hidden="true"></i> Soporte 24/7</span>
              <span class="hero-float-badge"><i class="bi bi-award text-brand" aria-hidden="true"></i> +5 Años de Experiencia</span>
            </div>
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
