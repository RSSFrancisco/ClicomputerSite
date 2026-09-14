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

      geometry = {
        top, originY, start,
        originX: earth.left - bounds.left + earth.width / 2,
        centerX: bounds.width / 2,
        amplitude: Math.min(440, Math.max(0, bounds.width / 2 - 30)),
        wavelength: Math.max(900, window.innerHeight * 1.6),
        distance: Math.max(0, endY - originY),
        scrollRange: Math.max(1, maxScroll - start)
      };
      needsMeasure = false;
    }

    // Una onda seno continua; al despegar, se mezcla con la posición real de la Tierra.
    function position(distance) {
      const g = geometry;
      const blend = 1 - Math.exp(-distance / 380);
      const waveX = g.centerX - Math.sin(distance / g.wavelength * Math.PI * 2) * g.amplitude;
      return { x: g.originX + (waveX - g.originX) * blend, y: g.originY + distance };
    }

    function render() {
      frame = 0;
      if (needsMeasure) measure();

      const progress = Math.max(0, Math.min(1, (window.scrollY - geometry.start) / geometry.scrollRange));
      const distance = progress * geometry.distance;
      const point = position(distance);
      const next = position(distance + 1);
      const angle = Math.atan2(next.y - point.y, next.x - point.x) * 180 / Math.PI + 90;
      const opacity = Math.min(1, distance / 90) * .85;
      const viewportY = geometry.top + point.y - window.scrollY;

      $rocket.css({
        transform: `translate3d(${point.x}px, ${point.y}px, 0) rotate(${angle}deg)`,
        opacity
      });
      $scene.toggleClass('is-flying', opacity > 0 && viewportY > -120 && viewportY < window.innerHeight + 120);

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
      if (!frame && !motion.matches && !document.hidden) {
        frame = window.requestAnimationFrame(render);
      }
    }

    function refresh() {
      needsMeasure = true;
      const enabled = !motion.matches && !document.hidden;
      $scene.toggleClass('is-enabled', enabled);
      if (!enabled) {
        window.cancelAnimationFrame(frame);
        frame = 0;
        $scene.removeClass('is-flying');
        return;
      }
      schedule();
    }

    window.addEventListener('scroll', schedule, { passive: true });
    $(window).on('resize.spaceJourney load.spaceJourney pageshow.spaceJourney', refresh);
    $(document).on('visibilitychange.spaceJourney', refresh);
    motion.addEventListener('change', refresh);
    if ('ResizeObserver' in window) {
      const observer = new ResizeObserver(refresh);
      observer.observe(main);
      observer.observe(globe);
    }
    refresh();
  });
})(window.jQuery);
