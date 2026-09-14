const NavbarController = (() => {
  'use strict';
  function init() {
    const navbar = document.querySelector('.cli-navbar');
    if (!navbar) return;
    const menu = navbar.querySelector('.navbar-collapse');
    const toggle = navbar.querySelector('.navbar-toggler');
    if (menu && toggle && window.bootstrap?.Collapse) {
      menu.classList.add('collapse');
      toggle.hidden = false;
      navbar.classList.add('navigation-ready');
      navbar.querySelectorAll('.nav-link').forEach((link) => {
        link.addEventListener('click', () => window.bootstrap.Collapse.getInstance(menu)?.hide());
      });
    }
    const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 50);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }
  return { init };
})();
