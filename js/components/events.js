(() => {
  'use strict';

  const countdown = document.querySelector('[data-countdown]');
  if (countdown) {
    const start = Date.parse(countdown.dataset.countdown);
    const units = ['days', 'hours', 'minutes', 'seconds'];
    const fields = units.map(unit => countdown.querySelector(`[data-count="${unit}"]`));
    let timer;
    const update = () => {
      const remaining = Math.max(0, Math.floor((start - Date.now()) / 1000));
      const values = [Math.floor(remaining / 86400), Math.floor(remaining / 3600) % 24, Math.floor(remaining / 60) % 60, remaining % 60];
      fields.forEach((field, index) => {
        const value = String(values[index]).padStart(2, '0');
        if (field.textContent !== value) field.textContent = value;
      });
      if (remaining === 0) {
        const message = document.getElementById('countdown-message');
        message.textContent = Date.now() < start + 86400000 ? '¡Llegó el gran día! Celebremos juntos.' : 'Gracias por ser parte de este día tan especial.';
        clearInterval(timer);
      }
    };
    const resume = () => {
      clearInterval(timer);
      update();
      if (Date.now() < start && !document.hidden) timer = setInterval(update, 1000);
    };
    if (Number.isFinite(start)) {
      resume();
      document.addEventListener('visibilitychange', resume);
    }
  }

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  if ('IntersectionObserver' in window && !reducedMotion.matches) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.12 });
    document.querySelectorAll('[data-reveal]').forEach(element => {
      element.classList.add('reveal-ready');
      observer.observe(element);
    });
    const showAll = () => {
      if (!reducedMotion.matches) return;
      document.querySelectorAll('.reveal-ready').forEach(element => element.classList.add('is-visible'));
      observer.disconnect();
    };
    if (reducedMotion.addEventListener) reducedMotion.addEventListener('change', showAll);
    else if (reducedMotion.addListener) reducedMotion.addListener(showAll);
  }

  const dialog = document.querySelector('[data-invitation-dialog]');
  const opener = document.querySelector('[data-invitation-open]');
  if (dialog && opener && typeof dialog.showModal === 'function') {
    opener.addEventListener('click', event => {
      event.preventDefault();
      dialog.showModal();
      document.body.classList.add('dialog-open');
    });
    dialog.querySelector('[data-invitation-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => {
      const bounds = dialog.getBoundingClientRect();
      if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close();
    });
    dialog.addEventListener('close', () => {
      document.body.classList.remove('dialog-open');
      opener.focus({ preventScroll: true });
    });
  }
})();
