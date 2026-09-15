/** Cohete decorativo: una actualización por frame solicitado, sin bucle continuo. */
(function ($) {
  'use strict';
  if (!$) return;

  $(function () {
    const $scene = $('[data-space-journey]');
    const globe = $('.network-globe')[0];
    if (!$scene.length || !globe) return;

    const main = $scene.parent()[0];
    const $rocket = $scene.find('.space-rocket');
    const sparks = $scene.find('.space-journey-spark').toArray();
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let geometry;
    let frame = 0;
    let needsMeasure = true;

    // Las medidas se leen al cargar o redimensionar, nunca en cada scroll.
    function measure() {
      const bounds = main.getBoundingClientRect();
      const earth = globe.getBoundingClientRect();
      const top = bounds.top + window.scrollY;
      const originY = earth.top - bounds.top + earth.height * .72;
      const maxScroll = Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
      const start = Math.max(0, top + originY - window.innerHeight * .68);
      const endY = Math.min(bounds.height - 80, maxScroll - top + window.innerHeight * .68);
      // El recorrido termina en main: un footer alto en móvil no debe adelantar el cohete.
      const endScroll = Math.min(maxScroll, top + endY - window.innerHeight * .68);
      const mobile = window.innerWidth < 768;
      const originX = earth.left - bounds.left + earth.width / 2;
      // El borde real de la tarjeta sigue el ancho Bootstrap, también entre 576 y 767 px.
      const card = mobile ? main.querySelector('.service-card') : null;
      const borderX = card ? card.getBoundingClientRect().right - bounds.left : bounds.width - 12;

      geometry = {
        top, originY, start,
        originX,
        // Se superpone un poco al borde; 24 px contienen las aletas dentro de la pantalla.
        centerX: mobile ? Math.max(24, Math.min(borderX - 4, bounds.width - 24)) : bounds.width / 2,
        amplitude: mobile ? 6 : Math.min(440, Math.max(0, bounds.width / 2 - 30)),
        launchDistance: mobile ? 100 : 380,
        rest: { x: Math.min(bounds.width - 32, originX + earth.width * .45), y: originY + earth.height * .28 + 24 },
        wavelength: Math.max(900, window.innerHeight * 1.6),
        distance: Math.max(0, endY - originY),
        scrollRange: Math.max(1, endScroll - start)
      };
      needsMeasure = false;
    }

    // Una onda seno continua; al despegar, se mezcla con la posición real de la Tierra.
    function position(distance) {
      const g = geometry;
      const blend = 1 - Math.exp(-distance / g.launchDistance);
      const waveX = g.centerX - Math.sin(distance / g.wavelength * Math.PI * 2) * g.amplitude;
      return { x: g.originX + (waveX - g.originX) * blend, y: g.originY + distance };
    }

    function render() {
      frame = 0;
      if (needsMeasure) measure();

      const reduced = motion.matches;
      const progress = reduced ? 0 : Math.max(0, Math.min(1, (window.scrollY - geometry.start) / geometry.scrollRange));
      const distance = progress * geometry.distance;
      const point = reduced ? geometry.rest : position(distance);
      const next = position(distance + 1);
      const angle = reduced ? 155 : Math.atan2(next.y - point.y, next.x - point.x) * 180 / Math.PI + 90;
      const opacity = reduced ? .7 : Math.min(1, distance / 90) * .85;
      const viewportY = geometry.top + point.y - window.scrollY;

      $rocket.css({
        transform: `translate3d(${point.x}px, ${point.y}px, 0) rotate(${angle}deg)`,
        opacity
      });
      $scene.toggleClass('is-flying', !reduced && opacity > 0 && viewportY > -120 && viewportY < window.innerHeight + 120);

      // Seis puntos reutilizados siguen la misma curva, incluso al volver hacia arriba.
      sparks.forEach(function (spark, index) {
        const behind = distance - 65 - index * 25;
        const trail = position(Math.max(0, behind));
        $(spark).css({
          transform: `translate3d(${trail.x}px, ${trail.y}px, 0)`,
          opacity: behind > 0 ? opacity * (1 - index / sparks.length) * .5 : 0
        });
      });
    }

    function schedule() {
      if (!frame && !document.hidden) {
        frame = window.requestAnimationFrame(render);
      }
    }

    function refresh() {
      needsMeasure = true;
      const enabled = !document.hidden;
      $scene.toggleClass('is-enabled', enabled);
      if (!enabled) {
        window.cancelAnimationFrame(frame);
        frame = 0;
        $scene.removeClass('is-flying');
        return;
      }
      schedule();
    }

    window.addEventListener('scroll', function () {
      if (!motion.matches) schedule();
    }, { passive: true });
    $(window).on('resize.spaceJourney load.spaceJourney pageshow.spaceJourney', refresh);
    $(document).on('visibilitychange.spaceJourney', refresh);
    if (motion.addEventListener) motion.addEventListener('change', refresh);
    else if (motion.addListener) motion.addListener(refresh);
    if (window.visualViewport) window.visualViewport.addEventListener('resize', refresh);
    if ('ResizeObserver' in window) {
      const observer = new ResizeObserver(refresh);
      observer.observe(main);
      observer.observe(globe);
    }
    refresh();
  });
})(window.jQuery);
