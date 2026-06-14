/**
 * Clicomputer México — Theme Switcher
 * Toggle dark/light mode con persistencia en localStorage
 * Respeta prefers-color-scheme del sistema como default
 */
const ThemeSwitcher = (() => {
  'use strict';

  let toggleBtn = null;

  function getSystemPreference() {
    return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
  }

  function getSavedTheme() {
    return localStorage.getItem('cli-theme') || getSystemPreference();
  }

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    if (toggleBtn) {
      const icon = toggleBtn.querySelector('i');
      if (icon) {
        icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
      }
    }
  }

  function toggle() {
    const current = document.documentElement.getAttribute('data-bs-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    applyTheme(next);
    localStorage.setItem('cli-theme', next);
  }

  function init() {
    applyTheme(getSavedTheme());
    toggleBtn = document.getElementById('themeToggle');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', toggle);
    }

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
      if (!localStorage.getItem('cli-theme')) {
        applyTheme(e.matches ? 'dark' : 'light');
      }
    });
  }

  return { init, toggle, applyTheme };
})();
