/**
 * Clicomputer México — Navbar Controller
 * Scroll effects, active state, mobile menu handling
 */
const NavbarController = (() => {
  'use strict';

  let navbar = null;
  let navLinks = [];
  let sections = [];

  function handleScroll() {
    if (!navbar) return;
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
    updateActiveLink();
  }

  function updateActiveLink() {
    const scrollPos = window.scrollY + 100;
    sections.forEach((section) => {
      const top = section.offsetTop;
      const height = section.offsetHeight;
      const id = section.getAttribute('id');
      if (scrollPos >= top && scrollPos < top + height) {
        navLinks.forEach((link) => {
          link.classList.remove('active');
          if (link.getAttribute('href') === '#' + id) {
            link.classList.add('active');
          }
        });
      }
    });
  }

  function handleMobileClose() {
    const navCollapse = document.querySelector('.navbar-collapse');
    if (!navCollapse) return;
    navLinks.forEach((link) => {
      link.addEventListener('click', () => {
        const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
        if (bsCollapse) bsCollapse.hide();
      });
    });
  }

  function init() {
    navbar = document.querySelector('.cli-navbar');
    navLinks = document.querySelectorAll('.cli-navbar .nav-link');
    sections = document.querySelectorAll('section[id]');

    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
    handleMobileClose();
  }

  return { init };
})();
