/* El desplazamiento mueve una sola maqueta, también al regresar hacia arriba. */
(function ($) {
  'use strict';
  if (!$) return;

  $(function () {
    const $layer = $('[data-pc-assembly]');
    if (!$layer.length) return;

    const page = $layer.parent()[0];
    const $position = $layer.find('.pc-position');
    const intro = page.querySelector('.service-intro');
    const related = page.querySelector('.related-services');
    if (!intro || !related) return;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    let geometry, frame = 0, needsMeasure = true;

    function clamp(value, min, max) { return Math.max(min, Math.min(max, value)); }
    function phase(value, from, to) {
      const t = clamp((value - from) / (to - from), 0, 1);
      return t * t * (3 - 2 * t);
    }

    // Guardamos las líneas reales de texto. Las tarjetas opacas ya tapan el dibujo.
    function textBounds(bounds) {
      const range = document.createRange();
      const lines = [];
      page.querySelectorAll('#section-service h1, #section-service h2, #section-service h3, #section-service p, #section-service li, #section-service summary, #section-service a').forEach(function (element) {
        // Algunos navegadores devuelven rectángulos incluso para respuestas cerradas.
        if (element.closest('.service-card, details:not([open]) > :not(summary)') || !element.getClientRects().length) return;
        range.selectNodeContents(element);
        for (const rect of range.getClientRects()) {
          if (rect.width && rect.height) lines.push({
            left: rect.left - bounds.left, right: rect.right - bounds.left,
            top: rect.top - bounds.top, bottom: rect.bottom - bounds.top
          });
        }
      });
      return lines;
    }

    // Une las filas de tarjetas que no dejan espacio vertical para la ilustración.
    function cardBands(main, size) {
      const bands = [];
      const padding = 112 * size;
      for (const card of page.querySelectorAll('.service-card')) {
        const rect = card.getBoundingClientRect();
        const top = rect.top - main.top - padding;
        const bottom = rect.bottom - main.top + padding;
        const last = bands[bands.length - 1];
        if (last && top <= last.bottom) last.bottom = Math.max(last.bottom, bottom);
        else bands.push({ top, bottom });
      }
      return bands;
    }

    // El ensamble avanza en los tramos visibles y conserva la etapa detrás de las tarjetas.
    function exposedDistance(start, end, bands) {
      let distance = end - start;
      for (const band of bands) {
        distance -= Math.max(0, Math.min(end, band.bottom) - Math.max(start, band.top));
      }
      return distance;
    }

    function measure() {
      const main = page.getBoundingClientRect();
      const box = intro.getBoundingClientRect();
      const container = intro.parentElement.getBoundingClientRect();
      const heading = intro.querySelector('h1').getBoundingClientRect();
      const subtitle = intro.querySelector('.hero-subtitle').getBoundingClientRect();
      const badge = intro.querySelector('.section-badge').getBoundingClientRect();
      const lastSection = related.closest('section').getBoundingClientRect();
      const right = Math.min(main.right - 8, container.right + 45) - main.left;
      const gap = right - (box.right - main.left);
      const compact = gap < 225;
      const size = compact
        ? clamp((right - (badge.right - main.left) - 12) / 460, .23, .45)
        : Math.min(1.32, (gap - 16) / 455);
      const startX = compact ? right - 230 * size : right - gap / 2;
      const startY = (compact ? heading.top - 111 * size - 12 : (heading.top + subtitle.bottom) / 2) - main.top;
      const railSize = compact ? Math.min(.37, size) : Math.min(.94, size);
      const sectionY = lastSection.top - main.top + (compact ? 3 : lastSection.height * .5);
      // Deja sitio a la base del gabinete en su inclinación final antes del pie de página.
      const endY = Math.min(sectionY, main.height - 170 * railSize - 12);
      const pageTop = main.top + window.scrollY;
      const focusY = clamp(window.innerHeight * .52, 100, window.innerHeight * .75);
      const maxScroll = Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
      const bands = cardBands(main, railSize);
      geometry = {
        startX, startY, size, railSize, endY, compact, bands,
        endX: right - 145 * railSize - 8,
        travel: Math.max(1, Math.min(maxScroll, pageTop + endY - focusY)),
        exposed: exposedDistance(startY, endY, bands),
        lines: textBounds(main)
      };
      needsMeasure = false;
    }

    // La ilustración se atenúa gradualmente al pasar por detrás de una línea de texto.
    function foregroundClearance(x, y, width, height) {
      let overlap = 0;
      for (const line of geometry.lines) {
        const dx = Math.max(line.left - x, x - line.right, 0) / width;
        const dy = Math.max(line.top - y, y - line.bottom, 0) / height;
        overlap = Math.max(overlap, 1 - phase(Math.max(dx, dy), .64, 1.12));
        if (overlap === 1) break;
      }
      return overlap;
    }

    function render() {
      frame = 0;
      if (needsMeasure) measure();
      const g = geometry;
      const travel = reduced.matches ? 0 : clamp(window.scrollY / g.travel, 0, 1);
      const dock = phase(travel, .015, .24);
      const size = g.size + (g.railSize - g.size) * dock;
      const x = g.startX + (g.endX - g.startX) * dock;
      let y = g.startY + (g.endY - g.startY) * travel;
      const p = g.exposed > 0 ? clamp(exposedDistance(g.startY, y, g.bands) / g.exposed, 0, 1) : travel;
      // Mantiene cada pieza a la vista en el espacio que ya hay entre bloques.
      const band = g.bands.find(b => y > b.top && y < b.bottom);
      if (band) y = band.top + (band.bottom - band.top) * phase(y, band.top, band.bottom);
      const board = phase(p, .018, .30);
      const shell = phase(p, .64, .86);
      const overlap = foregroundClearance(x, y, size * 157, size * (99 + 47 * shell));
      $position.css({
        '--pc-x': x.toFixed(2) + 'px', '--pc-y': y.toFixed(2) + 'px',
        '--pc-size': size.toFixed(4), '--pc-ink': (.98 - overlap * (g.compact ? .85 : .80)).toFixed(3),
        '--board': board.toFixed(4), '--ram': phase(p, .28, .48).toFixed(4),
        '--cooler': phase(p, .47, .65).toFixed(4), '--case': shell.toFixed(4),
        '--glass': phase(p, .82, .96).toFixed(4)
      });
    }

    function schedule() {
      if (!frame && !document.hidden) frame = window.requestAnimationFrame(render);
    }
    function refresh() {
      needsMeasure = true;
      schedule();
    }
    $(window).on('scroll.pcAssembly', function () { if (!reduced.matches) schedule(); });
    $(window).on('resize.pcAssembly pageshow.pcAssembly load.pcAssembly', refresh);
    $(document).on('visibilitychange.pcAssembly', refresh);
    page.addEventListener('toggle', refresh, true);
    if (reduced.addEventListener) reduced.addEventListener('change', refresh);
    else reduced.addListener(refresh);
    if (window.ResizeObserver) {
      const observer = new ResizeObserver(refresh);
      observer.observe(page);
      observer.observe(intro);
    }
    if (document.fonts) document.fonts.ready.then(refresh);
    refresh();
  });
})(window.jQuery);
