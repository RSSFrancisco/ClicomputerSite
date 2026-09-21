const NavbarController = (() => {
  'use strict';
  function init() {
    const navbar = document.querySelector('.cli-navbar');
    if (!navbar) return;
    const menu = navbar.querySelector('.navbar-collapse');
    const toggle = navbar.querySelector('.navbar-toggler');
    if (menu && toggle) {
      menu.classList.add('collapse');
      toggle.hidden = false;
      navbar.classList.add('navigation-ready');
      if (!window.bootstrap?.Collapse) {
        toggle.removeAttribute('data-bs-toggle');
        toggle.addEventListener('click', () => {
          toggle.setAttribute('aria-expanded', String(menu.classList.toggle('show')));
        });
      }
      navbar.querySelectorAll('.nav-link').forEach((link) => {
        link.addEventListener('click', () => {
          if (window.bootstrap?.Collapse) window.bootstrap.Collapse.getInstance(menu)?.hide();
          else { menu.classList.remove('show'); toggle.setAttribute('aria-expanded', 'false'); }
        });
      });
    }
    const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 50);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }
  return { init };
})();
