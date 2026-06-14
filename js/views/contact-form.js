/**
 * Clicomputer México — Contact Form
 * Validación client-side con feedback visual
 * Preparado para enviar vía fetch() a endpoint PHP
 */
const ContactForm = (() => {
  'use strict';

  let form = null;

  function validate(field) {
    const value = field.value.trim();
    let isValid = true;
    let message = '';

    if (field.hasAttribute('required') && !value) {
      isValid = false;
      message = 'Este campo es requerido';
    } else if (field.type === 'email' && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        message = 'Ingresa un email válido';
      }
    } else if (field.type === 'tel' && value) {
      const phoneRegex = /^[\d\s\-\+\(\)]{7,15}$/;
      if (!phoneRegex.test(value)) {
        isValid = false;
        message = 'Ingresa un teléfono válido';
      }
    }

    toggleFieldState(field, isValid, message);
    return isValid;
  }

  function toggleFieldState(field, isValid, message) {
    const feedback = field.parentElement.querySelector('.invalid-feedback');
    if (isValid) {
      field.classList.remove('is-invalid');
      field.classList.add('is-valid');
    } else {
      field.classList.remove('is-valid');
      field.classList.add('is-invalid');
      if (feedback) feedback.textContent = message;
    }
  }

  async function handleSubmit(e) {
    e.preventDefault();
    const fields = form.querySelectorAll('.form-control, .form-select');
    let allValid = true;

    fields.forEach((field) => {
      if (!validate(field)) allValid = false;
    });

    if (!allValid) return;

    const submitBtn = form.querySelector('[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
    submitBtn.disabled = true;

    // Simular envío (reemplazar con fetch a PHP endpoint)
    try {
      await new Promise((resolve) => setTimeout(resolve, 1500));
      showAlert('success', '¡Mensaje enviado! Nos pondremos en contacto contigo pronto.');
      form.reset();
      fields.forEach((f) => f.classList.remove('is-valid', 'is-invalid'));
    } catch (error) {
      showAlert('danger', 'Error al enviar. Intenta nuevamente.');
    } finally {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  }

  function showAlert(type, message) {
    const alertContainer = document.getElementById('formAlerts');
    if (!alertContainer) return;
    alertContainer.innerHTML = `
      <div class="alert alert-${type} alert-dismissible fade show" role="alert">
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>`;
  }

  function init() {
    form = document.getElementById('contactForm');
    if (!form) return;

    form.addEventListener('submit', handleSubmit);
    const fields = form.querySelectorAll('.form-control, .form-select');
    fields.forEach((field) => {
      field.addEventListener('blur', () => validate(field));
    });
  }

  return { init };
})();
