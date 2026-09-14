const ThemeSwitcher = (() => {
  'use strict';
  function savedTheme() {
    try { return localStorage.getItem('cli-theme'); } catch (_) { return null; }
  }
  function applyTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    document.querySelectorAll('.theme-toggle').forEach((button) => {
      button.querySelector('i').className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
      button.setAttribute('aria-label', theme === 'dark' ? 'Activar tema claro' : 'Activar tema oscuro');
    });
  }
  function toggle() {
    const theme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    applyTheme(theme);
    try { localStorage.setItem('cli-theme', theme); } catch (_) { /* Optional persistence. */ }
  }
  function init() {
    const preference = window.matchMedia('(prefers-color-scheme: light)');
    const saved = savedTheme();
    applyTheme(saved === 'dark' || saved === 'light' ? saved : preference.matches ? 'light' : 'dark');
    document.querySelectorAll('.theme-toggle').forEach((button) => {
      button.addEventListener('click', toggle);
      button.hidden = false;
    });
    preference.addEventListener('change', () => {
      if (!savedTheme()) applyTheme(preference.matches ? 'light' : 'dark');
    });
  }
  return { init, toggle, applyTheme };
})();
