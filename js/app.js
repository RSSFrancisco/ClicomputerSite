/**
 * Clicomputer México — App Main Entry Point
 * Inicializa todos los módulos y carga las vistas
 */
const App = (() => {
  'use strict';

  async function loadViews() {
    // Load partials
    await ViewLoader.loadPartial('navbar', '#navbar-container');
    await ViewLoader.loadPartial('footer', '#footer-container');

    // Load page sections
    await ViewLoader.load('home', '#section-home');
    await ViewLoader.load('services', '#section-services');
    await ViewLoader.load('about', '#section-about');
    await ViewLoader.load('projects', '#section-projects');
    await ViewLoader.load('contact', '#section-contact');
  }

  function initModules() {
    ThemeSwitcher.init();
    NavbarController.init();
    Animations.init();
    ContactForm.init();
    MouseFollower.init();
    SearchController.init();
    initProjectFilters();
    initSmoothScroll();
  }

  function initProjectFilters() {
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('filter-btn')) {
        const filterBtns = document.querySelectorAll('.filter-btn');
        filterBtns.forEach((btn) => btn.classList.remove('active'));
        e.target.classList.add('active');

        const filter = e.target.getAttribute('data-filter');
        const cards = document.querySelectorAll('.project-card');
        cards.forEach((card) => {
          if (filter === 'all' || card.getAttribute('data-category') === filter) {
            card.closest('.col').style.display = '';
            setTimeout(() => card.style.opacity = '1', 50);
          } else {
            card.style.opacity = '0';
            setTimeout(() => card.closest('.col').style.display = 'none', 300);
          }
        });
      }
    });
  }

  function initSmoothScroll() {
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href^="#"]');
      if (!link) return;
      e.preventDefault();
      const target = document.querySelector(link.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }

  async function init() {
    // Apply theme immediately to prevent flash
    const savedTheme = localStorage.getItem('cli-theme') ||
      (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
    document.documentElement.setAttribute('data-bs-theme', savedTheme);

    await loadViews();
    initModules();

    // Remove loading state
    document.body.classList.add('loaded');
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  return { init };
})();
