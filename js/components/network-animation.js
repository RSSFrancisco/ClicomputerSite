/** Control compartido de las ilustraciones CSS de Tierra y videovigilancia. */
(function ($) {
  'use strict';
  if (!$) return;

  $(function () {
    $('[data-network-animation]').each(function () {
      const $network = $(this);
      const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
      let visible = !('IntersectionObserver' in window);

      function update() {
        $network.toggleClass('is-running', visible && !motion.matches && !document.hidden);
      }

      $(document).on('visibilitychange.networkAnimation', update);
      motion.addEventListener('change', update);

      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
          visible = entries[0].isIntersecting;
          update();
        }, { threshold: 0 });
        observer.observe($network[0]);
      }

      update();
    });
  });
})(window.jQuery);
