/** Bootstrap posiciona la información; jQuery coordina mouse, clic y teclado. */
(function ($) {
  'use strict';
  if (!$ || !window.bootstrap?.Popover) return;

  $(function () {
    const $buttons = $('[data-service-popover]');
    if (!$buttons.length) return;

    let active = null;
    let pinned = false;
    let hideTimer;

    function close() {
      clearTimeout(hideTimer);
      if (active) {
        active.popover.hide();
        active.$button.attr('aria-expanded', 'false');
      }
      active = null;
      pinned = false;
    }

    function open(entry) {
      clearTimeout(hideTimer);
      if (active === entry) return;
      close();
      active = entry;
      entry.popover.show();
      entry.$button.attr('aria-expanded', 'true');
    }

    function hideLater() {
      clearTimeout(hideTimer);
      if (pinned || active?.$button.is(':focus')) return;
      // Da tiempo para mover el cursor desde el botón hasta el texto.
      hideTimer = setTimeout(close, 180);
    }

    $buttons.each(function () {
      const $button = $(this);
      // Se insertan nodos con texto, nunca HTML procedente de los atributos.
      const $title = $('<span>', {class: 'd-flex align-items-center gap-2'})
        .append($button.children('.bi').clone(), $('<span>').text($button.find('.network-label-text').text()));
      const $content = $('<p>', {class: 'mb-0'}).text($button.attr('title'));
      const entry = {
        $button,
        popover: new window.bootstrap.Popover(this, {
          trigger: 'manual', container: 'body', placement: 'top', boundary: document.body,
          customClass: 'network-service-popover', animation: false, html: true,
          title: $title[0], content: $content[0]
        })
      };

      $button.attr('aria-expanded', 'false')
        .on('pointerenter.servicePopover', function (event) {
          if (event.originalEvent.pointerType !== 'touch') open(entry);
        })
        .on('pointerleave.servicePopover', function () {
          if (active === entry) hideLater();
        })
        .on('focusin.servicePopover', function () { open(entry); })
        .on('focusout.servicePopover', function () {
          if (active === entry) { pinned = false; hideLater(); }
        })
        .on('click.servicePopover', function () {
          if (active === entry && pinned) close();
          else { open(entry); pinned = true; }
        });
    });

    $(document)
      .on('pointerdown.servicePopover', function (event) {
        if (!$(event.target).closest('[data-service-popover], .network-service-popover').length) close();
      })
      .on('keydown.servicePopover', function (event) {
        if (event.key === 'Escape') close();
      })
      .on('mouseenter.servicePopover', '.network-service-popover', function () { clearTimeout(hideTimer); })
      .on('mouseleave.servicePopover', '.network-service-popover', hideLater)
      .on('visibilitychange.servicePopover', function () { if (document.hidden) close(); });

    $(window).on('scroll.servicePopover resize.servicePopover', function () {
      if (!active) return;
      const bounds = active.$button[0].getBoundingClientRect();
      if (bounds.bottom < 0 || bounds.top > window.innerHeight) close();
    });
  });
})(window.jQuery);
