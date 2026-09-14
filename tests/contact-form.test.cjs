const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function setup(values, valid = true, destination = 'https://wa.me/526567514187') {
  const handlers = {};
  const fields = Object.keys(values).map(name => ({ value: values[name], setCustomValidity() {} }));
  const form = {
    hidden: true, dataset: { whatsapp: destination }, values,
    addEventListener(name, callback) { handlers[name] = callback; },
    querySelectorAll() { return fields; }, reportValidity() { return valid; }
  };
  const link = { hidden: true, focus() {}, removeAttribute(name) { delete this[name]; } };
  const status = { textContent: '' };
  const context = vm.createContext({
    document: { getElementById(id) { return { contactForm: form, preparedWhatsApp: link, formAlerts: status }[id]; } },
    FormData: class { constructor(form) { return Object.entries(form.values); } }
  });
  vm.runInContext(fs.readFileSync(path.join(__dirname, '../js/views/contact-form.js'), 'utf8'), context);
  vm.runInContext('ContactForm.init()', context);
  return { context, handlers, form, link, status };
}

test('a valid draft preserves accents, line breaks and special characters without sending or clearing', () => {
  const values = { name: ' Ana Pérez ', service: 'Desarrollo web', email: '', phone: '', message: 'Catálogo & soporte / redes\n¿Pueden cotizar?' };
  const { form, handlers, link, status } = setup(values);
  let prevented = false;
  handlers.submit({ preventDefault() { prevented = true; } });
  assert.equal(prevented, true);
  assert.equal(form.hidden, false);
  assert.equal(link.hidden, false);
  const url = new URL(link.href);
  assert.equal(url.origin + url.pathname, 'https://wa.me/526567514187');
  const text = url.searchParams.get('text');
  assert.match(text, /Nombre: Ana Pérez/);
  assert.ok(text.endsWith(values.message));
  assert.doesNotMatch(text, /Email:|Teléfono:/);
  assert.equal(form.values, values);
  assert.doesNotMatch(status.textContent, /mensaje enviado/i);
  assert.match(status.textContent, /pulsa Enviar/);
});

test('an invalid form does not prepare a link', () => {
  const { handlers, link, status } = setup({ name: '', service: '', message: '' }, false);
  handlers.submit({ preventDefault() {} });
  assert.equal(link.hidden, true);
  assert.equal(link.href, undefined);
  assert.equal(status.textContent, '');
});

test('editing a prepared draft invalidates the old link', () => {
  const { handlers, link, status } = setup({ name: 'Ana', service: 'Redes', message: 'Una red' });
  handlers.submit({ preventDefault() {} });
  handlers.input({ target: { setCustomValidity() {} } });
  assert.equal(link.hidden, true);
  assert.equal(link.href, undefined);
  assert.equal(status.textContent, '');
});

test('an unexpected destination cannot receive form data', () => {
  const { form, handlers } = setup({}, true, 'https://example.com/collect');
  assert.equal(form.hidden, true);
  assert.equal(handlers.submit, undefined);
});
