/** Static HTML is the source of content; JavaScript only enhances interaction. */
const App = (() => {
  'use strict';
  function initProjectFilters() {
    const filters = document.querySelector('.project-filters');
    if (!filters) return;
    filters.addEventListener('click', (event) => {
      const button = event.target.closest('[data-filter]');
      if (!button) return;
      filters.querySelectorAll('[data-filter]').forEach((item) => {
        const selected = item === button;
        item.classList.toggle('active', selected);
        item.setAttribute('aria-pressed', String(selected));
      });
      document.querySelectorAll('.project-card').forEach((card) => {
        card.parentElement.hidden = button.dataset.filter !== 'all' && card.dataset.category !== button.dataset.filter;
      });
    });
    filters.querySelectorAll('[data-filter]').forEach((button) => {
      button.setAttribute('aria-pressed', String(button.classList.contains('active')));
    });
    filters.hidden = false;
    function showLinkedProject() {
      if (!window.location.hash.startsWith('#proyecto-')) return;
      const target = document.getElementById(window.location.hash.slice(1));
      if (!target?.matches('.project-card')) return;
      // Un filtro previo no debe ocultar un proyecto seleccionado desde la búsqueda.
      filters.querySelector('[data-filter="all"]').click();
      target.scrollIntoView({ block: 'start' });
    }
    window.addEventListener('hashchange', showLinkedProject);
    document.addEventListener('click', (event) => {
      const link = event.target.closest('.site-search-result');
      // Repetir el mismo enlace no dispara hashchange, pero debe quitar un filtro nuevo.
      if (link?.href === window.location.href && !event.ctrlKey && !event.metaKey && !event.shiftKey && !event.altKey) {
        showLinkedProject();
      }
    });
    showLinkedProject();
  }
  function init() {
    const modules = [
      () => ContactForm.init(), () => ThemeSwitcher.init(), () => NavbarController.init(),
      () => Animations.init(), () => SearchController.init(),
      initProjectFilters
    ];
    modules.forEach((initModule) => {
      try { initModule(); } catch (error) { console.warn('Clicomputer: mejora opcional no disponible.', error); }
    });
    const year = document.getElementById('currentYear');
    if (year) year.textContent = new Date().getFullYear();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
  return { init };
})();
