/** Búsqueda progresiva: PHP resuelve las consultas; jQuery controla la interfaz. */
const SearchController = (() => {
  'use strict';

  function init() {
    const $ = window.jQuery;
    // La búsqueda por formulario sigue disponible si no se puede mejorar con JavaScript.
    if (!$ || !window.fetch || !window.AbortController) return;
    const $form = $('#siteSearchForm');
    if (!$form.length) return;
    const $input = $form.find('#globalSearch');
    const $panel = $form.find('#searchPanel');
    const $list = $form.find('#searchSuggestions');
    const $status = $form.find('#searchStatus');
    const $clear = $form.find('.nav-search-clear');
    const $all = $form.find('#searchAll');
    let timer = 0;
    let controller = null;
    let requestId = 0;
    let active = -1;

    $form.addClass('is-search-ready');
    $input.attr({ role: 'combobox', 'aria-autocomplete': 'list', 'aria-controls': 'searchSuggestions', 'aria-expanded': 'false' });

    function updateClear() {
      const hasQuery = $input.val().length > 0;
      $clear.prop('hidden', !hasQuery);
      $form.toggleClass('has-query', hasQuery);
    }

    function cancel() {
      clearTimeout(timer);
      if (controller) controller.abort();
      controller = null;
      // También invalida una respuesta que ya terminó justo antes de abortar.
      requestId++;
    }

    function close() {
      cancel();
      active = -1;
      $panel.prop('hidden', true);
      $input.attr('aria-expanded', 'false').removeAttr('aria-activedescendant');
      $list.attr('aria-busy', 'false');
    }

    function show() {
      $panel.prop('hidden', false);
      $input.attr('aria-expanded', 'true');
    }

    function hideMobileMenu() {
      const menu = document.getElementById('navbarMain');
      if (window.innerWidth < 1200 && menu) window.bootstrap?.Collapse?.getInstance(menu)?.hide();
    }

    function render(data) {
      $list.empty();
      active = -1;
      if (!Array.isArray(data.results)) throw new Error('Respuesta inválida');
      data.results.slice(0, 5).forEach(function (item, index) {
        // Los enlaces son internos y los textos se crean como nodos, nunca como HTML recibido.
        if (typeof item.url !== 'string' || !item.url.startsWith('/') ||
          new URL(item.url, window.location.origin).origin !== window.location.origin) return;
        const $link = $('<a>').addClass('site-search-result').attr({
          href: item.url, role: 'option', id: 'search-option-' + index, tabindex: '-1', 'aria-selected': 'false'
        });
        const icon = /^bi-[a-z0-9-]+$/.test(item.icon) ? item.icon : 'bi-search';
        const $icon = $('<span>').addClass('site-search-result-icon').attr('aria-hidden', 'true')
          .append($('<i>').addClass('bi ' + icon));
        const $copy = $('<span>').addClass('site-search-result-copy')
          .append($('<span>').addClass('site-search-category').text(item.category))
          .append($('<span>').addClass('site-search-title').text(item.title))
          .append($('<span>').addClass('site-search-description').text(item.description));
        $list.append($('<li>').attr('role', 'presentation').append($link.append($icon, $copy)));
      });
      const count = $list.children().length;
      $status.text(count ? 'Mostrando ' + count + ' de ' + data.total + ' resultados.' :
        'No encontramos resultados. Prueba con otras palabras.');
    }

    async function lookup(query, version) {
      controller = new AbortController();
      const url = new URL($form[0].action);
      url.searchParams.set('q', query);
      url.searchParams.set('format', 'json');
      try {
        // jQuery Slim no incluye AJAX; fetch evita cargar otra biblioteca.
        const response = await fetch(url, { signal: controller.signal, headers: { Accept: 'application/json' } });
        const data = await response.json();
        if (version !== requestId) return;
        if (!response.ok) throw new Error(data.error || 'No pudimos obtener las sugerencias.');
        render(data);
      } catch (error) {
        if (version !== requestId || error.name === 'AbortError') return;
        $list.empty();
        $status.text('No pudimos cargar las sugerencias. Pulsa Enter para buscar.');
      } finally {
        if (version === requestId) {
          controller = null;
          $list.attr('aria-busy', 'false');
        }
      }
    }

    function queueSearch() {
      cancel();
      active = -1;
      updateClear();
      $list.empty().attr('aria-busy', 'false');
      $input.removeAttr('aria-activedescendant');
      const query = $input.val().trim();
      if (query.length < 2) { close(); return; }
      const url = new URL($form[0].action);
      url.searchParams.set('q', query);
      $all.attr('href', url.pathname + url.search);
      $status.text('Buscando…');
      $list.attr('aria-busy', 'true');
      show();
      const version = requestId;
      // Espera una pausa breve al escribir y descarta consultas anteriores.
      timer = window.setTimeout(() => lookup(query, version), 250);
    }

    function select(index) {
      const options = $list.find('[role="option"]');
      if (!options.length) return;
      active = (index + options.length) % options.length;
      options.removeClass('is-active').attr('aria-selected', 'false');
      const option = options[active];
      $(option).addClass('is-active').attr('aria-selected', 'true');
      $input.attr('aria-activedescendant', option.id);
      option.scrollIntoView({ block: 'nearest', behavior: 'auto' });
    }

    $input.on('input.siteSearch', function (event) {
      if (!event.originalEvent?.isComposing) queueSearch();
    }).on('compositionend.siteSearch focus.siteSearch', queueSearch)
      .on('keydown.siteSearch', function (event) {
        if (event.originalEvent?.isComposing) return;
        if (event.key === 'Escape') { event.preventDefault(); close(); return; }
        if ($panel.prop('hidden')) return;
        const options = $list.find('[role="option"]');
        if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && options.length) {
          event.preventDefault();
          select(event.key === 'ArrowDown' ? active + 1 : active < 0 ? options.length - 1 : active - 1);
        } else if (event.key === 'Enter' && active >= 0 && options[active]) {
          event.preventDefault();
          options[active].click();
        }
      });

    $clear.on('click.siteSearch', function () {
      $input.val('');
      close();
      updateClear();
      $input[0].focus();
    });
    $form.on('submit.siteSearch', cancel).on('click.siteSearch', '.site-search-result, .site-search-all', function () {
      close();
      hideMobileMenu();
    });
    $(document).on('pointerdown.siteSearch focusin.siteSearch', function (event) {
      if (!$form[0].contains(event.target)) close();
    }).on('keydown.siteSearch', function (event) {
      if (event.key !== '/' || event.ctrlKey || event.metaKey || event.altKey ||
        event.target.closest('input, textarea, select, [contenteditable]')) return;
      event.preventDefault();
      const menu = document.getElementById('navbarMain');
      const focus = () => { $input[0].focus(); $input[0].scrollIntoView({ block: 'nearest' }); };
      // En móvil se abre primero el menú; enfocar un campo oculto no tiene efecto.
      if (menu && getComputedStyle(menu).display === 'none' && window.bootstrap?.Collapse) {
        $(menu).off('shown.bs.collapse.siteSearch').one('shown.bs.collapse.siteSearch', focus);
        window.bootstrap.Collapse.getOrCreateInstance(menu, { toggle: false }).show();
      } else focus();
    });
    $('#navbarMain').on('hide.bs.collapse.siteSearch', close);
    updateClear();
  }

  return { init };
})();
