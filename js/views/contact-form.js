/** Prepares a WhatsApp draft. A visitor explicitly opens and sends it. */
const ContactForm = (() => {
  'use strict';

  function buildMessage(values) {
    const clean = (value) => String(value || '').trim();
    const lines = ['Hola, Clicomputer. Me gustaría solicitar una cotización.',
      `Nombre: ${clean(values.name)}`, `Servicio: ${clean(values.service)}`];
    if (clean(values.email)) lines.push(`Email: ${clean(values.email)}`);
    if (clean(values.phone)) lines.push(`Teléfono: ${clean(values.phone)}`);
    lines.push('', clean(values.message));
    return lines.join('\n');
  }

  function init() {
    const form = document.getElementById('contactForm');
    const result = document.getElementById('formAlerts');
    const link = document.getElementById('preparedWhatsApp');
    if (!form || !result || !link || form.dataset.initialized) return;
    const destination = form.dataset.whatsapp;
    if (!/^https:\/\/wa\.me\/\d{10,15}$/.test(destination || '')) return;

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      form.querySelectorAll('[required]').forEach((field) => {
        field.setCustomValidity(field.value.trim() ? '' : 'Completa este campo.');
      });
      if (!form.reportValidity()) return;
      const values = Object.fromEntries(new FormData(form));
      link.href = `${destination}?text=${encodeURIComponent(buildMessage(values))}`;
      link.hidden = false;
      result.textContent = 'Tu mensaje está preparado. Ábrelo en WhatsApp, revísalo y pulsa Enviar para hacérnoslo llegar.';
      link.focus();
      globalThis.CliAnalytics?.track('quote_prepared');
    });
    form.addEventListener('input', (event) => {
      if (typeof event.target.setCustomValidity === 'function') event.target.setCustomValidity('');
      link.hidden = true;
      link.removeAttribute('href');
      result.textContent = '';
    });
    form.dataset.initialized = 'true';
    form.hidden = false;
  }

  return { init, buildMessage };
})();
