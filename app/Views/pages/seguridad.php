<section class="hero-section" id="inicio" style="padding-top: 40px; padding-bottom: 80px; min-height: auto;">
      <div class="bg-gradient-radial"></div>
      <div class="bg-dots-pattern"></div>
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <div class="hero-content">
              <div class="section-badge fade-in-up"><i class="bi bi-shield-lock"></i> Videovigilancia Inteligente</div>
              <h1 class="hero-title fade-in-up">Cámaras de seguridad <span class="text-gradient">en Córdoba, Veracruz</span></h1>
              <p class="hero-subtitle fade-in-up">Instalación de cámaras para hogares y negocios: cobertura, grabación y acceso remoto según las necesidades de tu inmueble. Revisamos el espacio para definir equipos y cableado.</p>
              <div class="hero-actions fade-in-up">
                <a href="/#contacto" class="btn btn-primary-custom"><i class="bi bi-chat-dots"></i> Cotizar Instalación</a>
                <a href="#instalaciones" class="btn btn-outline-custom">Ver instalaciones industriales</a>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <?= $view->render('partials/security-network') ?>
          </div>
        </div>
      </div>
    </section>

    <?= $view->render('partials/security-gallery', ['photos' => $page['gallery']]) ?>
    <?= $view->render('partials/service-scope', compact('page')) ?>

    <section class="services-section section-padding" style="background-color: var(--bg-subtle);">
      <div class="container">
        <div class="section-header text-center mb-5">
          <h2 class="fade-in-up">Características de nuestros <span class="text-gradient">sistemas</span></h2>
          <p class="fade-in-up">Ofrecemos tecnología de vanguardia para garantizar tu tranquilidad en todo momento.</p>
        </div>
        <div class="row g-4 stagger-children">
          <div class="col-lg-4 col-md-6">
            <div class="service-card fade-in-up text-center h-100" style="padding: 3rem 2rem;">
              <div class="service-card-icon mx-auto" style="width: 80px; height: 80px; font-size: 2.5rem;"><i class="bi bi-phone"></i></div>
              <h3 class="h4 mt-4">Monitoreo Remoto</h3>
              <p>Visualiza tus cámaras en tiempo real desde cualquier lugar del mundo a través de tu smartphone, tablet o computadora.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="service-card fade-in-up text-center h-100" style="padding: 3rem 2rem;">
              <div class="service-card-icon mx-auto" style="width: 80px; height: 80px; font-size: 2.5rem;"><i class="bi bi-eye"></i></div>
              <h3 class="h4 mt-4">Resolución Ultra HD</h3>
              <p>Cámaras con resolución desde Full HD 1080p hasta 4K, que ofrecen imágenes nítidas para reconocer rostros y matrículas.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="service-card fade-in-up text-center h-100" style="padding: 3rem 2rem;">
              <div class="service-card-icon mx-auto" style="width: 80px; height: 80px; font-size: 2.5rem;"><i class="bi bi-moon-stars"></i></div>
              <h3 class="h4 mt-4">Visión Nocturna</h3>
              <p>Tecnología infrarroja y sensores de baja luminosidad (ColorVu/Starlight) para capturar video claro incluso en la oscuridad total.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="service-card fade-in-up text-center h-100" style="padding: 3rem 2rem;">
              <div class="service-card-icon mx-auto" style="width: 80px; height: 80px; font-size: 2.5rem;"><i class="bi bi-person-bounding-box"></i></div>
              <h3 class="h4 mt-4">Detección Inteligente</h3>
              <p>Notificaciones y alertas inmediatas al detectar movimiento, intrusión en zonas restringidas o cruce de líneas virtuales.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="service-card fade-in-up text-center h-100" style="padding: 3rem 2rem;">
              <div class="service-card-icon mx-auto" style="width: 80px; height: 80px; font-size: 2.5rem;"><i class="bi bi-hdd-network"></i></div>
              <h3 class="h4 mt-4">Almacenamiento Seguro</h3>
              <p>Grabadores NVR/DVR de alta capacidad y opciones de respaldo en la nube para mantener tu evidencia protegida.</p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="service-card fade-in-up text-center h-100" style="padding: 3rem 2rem;">
              <div class="service-card-icon mx-auto" style="width: 80px; height: 80px; font-size: 2.5rem;"><i class="bi bi-shield-check"></i></div>
              <h3 class="h4 mt-4">Equipos Profesionales</h3>
              <p>Trabajamos exclusivamente con las mejores marcas del mercado como Hikvision y Dahua para asegurar durabilidad y confiabilidad.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section section-padding-sm">
      <div class="container">
        <div class="cta-card fade-in-up" style="background: linear-gradient(135deg, var(--bg-card), var(--bg-subtle)); border: 1px solid var(--brand-primary); box-shadow: 0 0 30px rgba(248,81,73,0.1);">
          <h2>Protege lo que más importa</h2>
          <p>Realizamos un levantamiento en tu sitio para diseñar el sistema de seguridad que mejor se adapte a tus necesidades y presupuesto.</p>
          <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
            <a href="/#contacto" class="btn btn-primary-custom">
              <i class="bi bi-chat-dots"></i> Agendar Visita Técnica
            </a>
            <a href="<?= e('https://wa.me/' . ltrim($site['telephone'], '+')) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-custom">
              <i class="bi bi-whatsapp"></i> WhatsApp
            </a>
          </div>
        </div>
      </div>
    </section>

<section class="section-padding-sm"><div class="container service-faq"><h2 class="mb-4">Antes de instalar tu sistema CCTV</h2>
<details><summary>¿Qué necesitan para cotizar la instalación?</summary><p>Indícanos la ubicación, las áreas que quieres vigilar y si ya tienes cámaras o cableado. Una revisión del sitio permite definir equipos, recorridos y almacenamiento.</p></details>
<details><summary>¿Puedo ver las cámaras desde mi teléfono?</summary><p>Es posible con equipos compatibles y una conexión de internet adecuada. La configuración de acceso remoto y los permisos se acuerdan según el sistema elegido.</p></details>
<details><summary>¿Cuántos días de grabación puedo conservar?</summary><p>Depende del número de cámaras, resolución, capacidad del disco y modalidad de grabación. Definimos estas necesidades antes de seleccionar el grabador y el almacenamiento.</p></details>
<p class="mt-4">Consulta también nuestro servicio de <a href="/redes.html">redes y cableado estructurado</a> y los <a href="/proyectos.html">proyectos de videovigilancia</a>.</p></div></section>
