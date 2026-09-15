/** Bootstrap gestiona el diálogo, Escape y el foco; jQuery actualiza la foto. */
(function ($) {
  'use strict';
  if (!$ || !window.bootstrap?.Modal) return;

  $(function () {
    const $modal = $('#securityPhotoModal');
    if (!$modal.length) return;
    const $image = $modal.find('.security-photo-full');

    $modal.on('show.bs.modal', function (event) {
      const link = event.relatedTarget;
      if (!link?.matches('[data-security-photo]')) return;
      const $figure = $(link).closest('figure');
      $modal.find('#securityPhotoTitle').text($figure.find('h3').text());
      $modal.find('#securityPhotoCaption').text($figure.find('figcaption p').text());
      $image.attr({ src: link.href, alt: $(link).find('img').attr('alt') }).prop('hidden', false);
    });

    $modal.on('hidden.bs.modal', function () {
      $image.prop('hidden', true).removeAttr('src').attr('alt', '');
    });

    // Sin JavaScript o Bootstrap, el enlace sigue abriendo la imagen completa.
    $('[data-security-photo]').attr({ 'data-bs-toggle': 'modal', 'data-bs-target': '#securityPhotoModal' });
  });
})(window.jQuery);
