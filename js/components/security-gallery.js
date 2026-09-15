/** Bootstrap gestiona el diálogo, Escape y el foco; jQuery actualiza la foto. */
(function ($) {
  'use strict';
  if (!$ || !window.bootstrap?.Modal) return;

  $(function () {
    function openLinkedGallery() {
      const gallery = document.getElementById('industrialPhotos');
      if (window.location.hash !== '#industrialPhotos' || !gallery || !window.bootstrap.Collapse) return;
      const scroll = () => document.getElementById('instalaciones').scrollIntoView({ block: 'start' });
      // El buscador puede enlazar al contenido cerrado: se abre antes de desplazar la página.
      if (gallery.classList.contains('show')) scroll();
      else {
        $(gallery).one('shown.bs.collapse', scroll);
        window.bootstrap.Collapse.getOrCreateInstance(gallery, { toggle: false }).show();
      }
    }
    $(window).on('hashchange.securityGallery', openLinkedGallery);
    $(document).on('click.securityGallery', '.site-search-result', function (event) {
      // Permite volver a abrir la galería si se cerró sin cambiar la URL.
      if (this.href === window.location.href && !event.ctrlKey && !event.metaKey && !event.shiftKey && !event.altKey) {
        openLinkedGallery();
      }
    });
    openLinkedGallery();

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
