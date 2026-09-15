<footer class="cli-footer" id="footer">
      <div class="container">
        <div class="row g-4">
          <div class="col-lg-4 col-md-6">
            <a class="footer-brand" href="/" aria-label="Clicomputer, volver al inicio"><?= $view->render('partials/brand') ?></a>
            <p>Páginas web, software, soporte técnico, redes y cámaras de seguridad para tu negocio en <?= e($site['locality'] . ', ' . $site['region']) ?>.</p>
            <div class="footer-social">



              <a href="<?= e('https://wa.me/' . ltrim($site['telephone'], '+')) ?>" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
            </div>
          </div>
          <div class="col-lg-2 col-md-6">
            <h5>Navegación</h5>
            <ul class="footer-links">
              <li><a href="/#inicio">Inicio</a></li>
              <li><a href="/#servicios">Servicios</a></li>
              <li><a href="/#nosotros">Nosotros</a></li>
              <li><a href="/seguridad.html">Seguridad</a></li>
              <li><a href="/proyectos.html">Proyectos</a></li>
              <li><a href="/#contacto">Contacto</a></li>
            </ul>
          </div>
          <div class="col-lg-3 col-md-6">
            <h5>Servicios</h5>
            <ul class="footer-links">
              <li><a href="/desarrollo-software.html">Desarrollo de Software</a></li>
              <li><a href="/desarrollo-web.html">Desarrollo Web</a></li>
              <li><a href="/soporte-tecnico.html">Soporte Técnico</a></li>
              <li><a href="/redes.html">Redes</a></li>
              <li><a href="/seguridad.html">Cámaras de Seguridad</a></li>
              <li><a href="/consultoria-ti.html">Consultoría TI</a></li>
            </ul>
          </div>
          <div class="col-lg-3 col-md-6">
            <h5>Contacto</h5>
            <ul class="footer-links">
              <li><i class="bi bi-geo-alt me-2 text-brand"></i>Córdoba, Veracruz, México</li>
              <li><a href="tel:<?= e($site['telephone']) ?>"><i class="bi bi-telephone me-2 text-brand"></i><?= e($site['telephone_display']) ?></a></li>
              <li><a href="mailto:<?= e($site['email']) ?>"><i
                    class="bi bi-envelope me-2 text-brand"></i><?= e($site['email']) ?></a></li>
              <li><i class="bi bi-clock me-2 text-brand"></i>Lun - Vie: 9:00 - 18:00</li>
            </ul>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; <span id="currentYear"><?= date('Y') ?></span> Clicomputer México. Todos los derechos reservados.</p>
        </div>
      </div>
    </footer>
