/**
 * Clicomputer México — Search Controller
 * Maneja la búsqueda global en el sitio
 */
const SearchController = (() => {
  'use strict';

  function init() {
    const searchInput = document.getElementById('globalSearch');
    if (!searchInput) return;
    searchInput.closest('.nav-search').hidden = false;

    // Atajo de teclado: "/" para buscar
    document.addEventListener('keydown', (e) => {
      if (e.key === '/' && !e.target.closest('input, textarea, select, [contenteditable="true"]')) {
        e.preventDefault();
        searchInput.focus();
      }
    });

    // Manejar entrada de búsqueda
    searchInput.addEventListener('input', debounce((e) => {
      const term = e.target.value.toLowerCase();
      if (term.length < 2) return;
      
      performSearch(term);
    }, 500));
  }

  function performSearch(term) {
    // Buscar en secciones principales
    const sections = ['#section-home', '#section-services', '#section-about', '#section-projects', '#section-seguridad', '#section-contact', '#servicio'];
    let bestMatch = null;

    for (const id of sections) {
      const el = document.querySelector(id);
      if (el && el.innerText.toLowerCase().includes(term)) {
        bestMatch = el;
        break;
      }
    }

    if (bestMatch) {
      bestMatch.scrollIntoView({ behavior: 'smooth' });
      // Efecto de resaltado temporal
      bestMatch.style.transition = 'outline 0.3s ease';
      bestMatch.style.outline = '2px solid var(--brand-primary)';
      setTimeout(() => {
        bestMatch.style.outline = 'none';
      }, 2000);
    }
  }

  function debounce(func, wait) {
    let timeout;
    return function(...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, args), wait);
    };
  }

  return { init };
})();
