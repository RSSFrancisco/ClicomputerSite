/**
 * Clicomputer México — Scroll Animations
 * IntersectionObserver para fade-in, counters, terminal typing
 */
const Animations = (() => {
  'use strict';

  function initScrollAnimations() {
    const elements = document.querySelectorAll('.fade-in-up, .fade-in-left, .fade-in-right, .scale-in');
    if (!elements.length) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    elements.forEach((el) => observer.observe(el));
  }

  function initCounters() {
    const counters = document.querySelectorAll('[data-counter]');
    if (!counters.length) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    counters.forEach((counter) => observer.observe(counter));
  }

  function animateCounter(el) {
    const target = parseInt(el.getAttribute('data-counter'), 10);
    const prefix = el.getAttribute('data-prefix') || '';
    const suffix = el.getAttribute('data-suffix') || '';
    const duration = 2000;
    const start = performance.now();

    function update(now) {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const current = Math.floor(eased * target);
      el.textContent = prefix + current.toLocaleString() + suffix;
      if (progress < 1) requestAnimationFrame(update);
    }

    requestAnimationFrame(update);
  }

  function initTerminalTyping() {
    const terminal = document.querySelector('.terminal-body');
    if (!terminal) return;

    const lines = [
      { type: 'prompt', text: '$ ' },
      { type: 'command', text: 'clicomputer --servicios' },
      { type: 'output', text: '' },
      { type: 'highlight', text: '→ Desarrollo de Software' },
      { type: 'highlight', text: '→ Desarrollo Web' },
      { type: 'highlight', text: '→ Soporte Técnico' },
      { type: 'highlight', text: '→ Redes e Infraestructura' },
      { type: 'highlight', text: '→ Cámaras de Seguridad' },
      { type: 'output', text: '' },
      { type: 'prompt', text: '$ ' },
      { type: 'string', text: '"Soluciones tecnológicas que impulsan tu negocio"' },
    ];

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          typeLines(terminal, lines);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.3 });

    observer.observe(terminal);
  }

  function typeLines(container, lines) {
    container.innerHTML = '';
    let lineIndex = 0;

    function nextLine() {
      if (lineIndex >= lines.length) {
        const cursor = document.createElement('span');
        cursor.className = 'terminal-cursor';
        container.lastElementChild.appendChild(cursor);
        return;
      }

      const line = lines[lineIndex];
      const div = document.createElement('div');
      div.className = 'terminal-line';

      const span = document.createElement('span');
      span.className = 'terminal-' + line.type;
      div.appendChild(span);
      container.appendChild(div);

      if (line.text === '') {
        lineIndex++;
        setTimeout(nextLine, 100);
        return;
      }

      let charIndex = 0;
      const speed = line.type === 'command' ? 50 : 20;

      function typeChar() {
        if (charIndex < line.text.length) {
          span.textContent += line.text[charIndex];
          charIndex++;
          setTimeout(typeChar, speed);
        } else {
          lineIndex++;
          setTimeout(nextLine, 200);
        }
      }

      typeChar();
    }

    nextLine();
  }

  function init() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) return;
    initScrollAnimations();
    initCounters();
    initTerminalTyping();
  }

  return { init };
})();
