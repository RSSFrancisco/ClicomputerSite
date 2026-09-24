/** Envía el formulario al servicio de correo; PHP también funciona sin JavaScript. */
const ContactForm = (() => {
  'use strict';

  function init() {
    const form = document.getElementById('contactForm');
    const result = document.getElementById('formAlerts');
    const button = form?.querySelector('button[type="submit"]');
    if (!form || !result || !button || form.dataset.initialized || typeof fetch !== 'function') return;
    const endpoint = new URL(form.action, window.location.href);
    if (endpoint.origin !== window.location.origin || endpoint.pathname !== '/contacto/enviar') return;
    endpoint.hash = '';
    endpoint.searchParams.set('format', 'json');
    const buttonText = button.textContent;
    let sending = false;

    function showStatus(message, success = false) {
      result.textContent = message;
      result.className = `mt-3 alert ${success ? 'alert-success' : 'alert-danger'}`;
      result.focus();
    }

    function clearErrors() {
      form.querySelectorAll('[aria-invalid]').forEach((field) => {
        field.removeAttribute('aria-invalid');
        field.removeAttribute('aria-describedby');
      });
      form.querySelectorAll('[id^="error-"]').forEach((error) => error.remove());
    }

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (sending) return;
      clearErrors();
      form.querySelectorAll('[required]').forEach((field) => {
        field.setCustomValidity(field.value.trim() ? '' : 'Completa este campo.');
      });
      if (!form.reportValidity()) return;
      const body = new FormData(form);
      // Congelar los campos evita borrar cambios escritos mientras llega la respuesta.
      const fields = [...form.elements];
      const disabled = fields.map((field) => field.disabled);
      fields.forEach((field) => { field.disabled = true; });
      sending = true;
      form.setAttribute('aria-busy', 'true');
      button.textContent = 'Enviando solicitud…';
      result.className = 'mt-3';
      result.textContent = 'Enviando tu solicitud por correo…';
      try {
        const response = await fetch(endpoint.href, {
          method: 'POST', body, headers: { Accept: 'application/json' }, credentials: 'same-origin',
        });
        const data = await response.json();
        if (response.ok && data.sent === true) {
          // Vaciar también los valores que PHP conservó tras un error anterior.
          form.querySelectorAll('input, textarea, select').forEach((field) => { field.value = ''; });
          showStatus(data.message, true);
          globalThis.CliAnalytics?.track('quote_sent');
        } else {
          const errors = Object.entries(data.errors || {})
            .filter(([name, message]) => ['name', 'email', 'phone', 'service', 'message'].includes(name) && typeof message === 'string');
          errors.forEach(([name, message]) => {
            const field = form.elements.namedItem(name);
            field?.setAttribute('aria-invalid', 'true');
            field?.setCustomValidity(message);
          });
          showStatus(typeof data.message === 'string' ? data.message : 'No pudimos enviar tu solicitud. Inténtalo de nuevo más tarde.');
        }
      } catch (_) {
        // No reintentar automáticamente: el servidor pudo aceptar el correo antes del corte.
        showStatus('No pudimos confirmar el envío. Tus datos se conservan; revisa tu conexión antes de volver a intentarlo.');
      } finally {
        sending = false;
        fields.forEach((field, index) => { field.disabled = disabled[index]; });
        form.removeAttribute('aria-busy');
        button.textContent = buttonText;
        if (form.querySelector('[aria-invalid]')) form.reportValidity();
      }
    });
    form.addEventListener('input', (event) => {
      if (typeof event.target.setCustomValidity === 'function') event.target.setCustomValidity('');
      event.target.removeAttribute('aria-invalid');
      event.target.removeAttribute('aria-describedby');
    });
    form.dataset.initialized = 'true';
  }

  return { init };
})();
