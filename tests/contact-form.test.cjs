const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const validValues = { name: 'Ana Pérez', email: 'ana@example.com', phone: '', service: 'Desarrollo web', message: 'Catálogo & redes\n¿Pueden cotizar?', website: '' };
function setup({ values = validValues, valid = true, fetchResult, action = '/contacto/enviar#contacto', hasFetch = true } = {}) {
  const handlers = {}, calls = [], events = [];
  const fields = Object.entries(values).map(([name, value]) => ({
    name, value, disabled: false, attributes: {}, validity: '', required: ['name', 'email', 'service', 'message'].includes(name),
    setCustomValidity(message) { this.validity = message; },
    setAttribute(name, value) { this.attributes[name] = value; },
    removeAttribute(name) { delete this.attributes[name]; }
  }));
  const button = { textContent: 'Enviar solicitud por correo', disabled: false };
  const elements = [...fields, button];
  elements.namedItem = name => fields.find(field => field.name === name);
  const form = {
    dataset: {}, action, elements, attributes: {},
    addEventListener(name, callback) { handlers[name] = callback; },
    querySelector(selector) { return selector.startsWith('button') ? button : fields.find(field => field.attributes['aria-invalid']); },
    querySelectorAll(selector) {
      if (selector === '[required]') return fields.filter(field => field.required);
      if (selector === '[aria-invalid]') return fields.filter(field => field.attributes['aria-invalid']);
      if (selector === '[id^="error-"]') return [];
      return fields;
    },
    reportValidity() { return valid && fields.every(field => !field.validity); },
    setAttribute(name, value) { this.attributes[name] = value; },
    removeAttribute(name) { delete this.attributes[name]; }
  };
  const status = { textContent: '', focus() {} };
  const context = vm.createContext({
    document: { getElementById(id) { return { contactForm: form, formAlerts: status }[id]; } },
    window: { location: { href: 'https://www.clcomputer.com/contacto.html', origin: 'https://www.clcomputer.com' } },
    URL,
    FormData: class { constructor(form) { this.entries = form.elements.filter(field => field.name && !field.disabled).map(field => [field.name, field.value]); } },
    ...(hasFetch ? { fetch: async (...args) => {
      calls.push(args);
      return fetchResult ? fetchResult(...args) : { ok: true, json: async () => ({ sent: true, message: 'Solicitud enviada al servicio de correo.' }) };
    } } : {}),
    CliAnalytics: { track: name => events.push(name) }
  });
  vm.runInContext(fs.readFileSync(path.join(__dirname, '../js/views/contact-form.js'), 'utf8'), context);
  vm.runInContext('ContactForm.init()', context);
  const submit = () => handlers.submit({ preventDefault() {} });
  return { context, handlers, form, fields, button, status, calls, events, submit };
}

test('posts all fields to the local mail endpoint, then clears them only after acceptance', async () => {
  const app = setup();
  await app.submit();
  assert.equal(app.calls.length, 1);
  const [url, request] = app.calls[0];
  assert.equal(url, 'https://www.clcomputer.com/contacto/enviar?format=json');
  assert.equal(request.method, 'POST');
  assert.equal(request.headers.Accept, 'application/json');
  assert.deepEqual(Object.fromEntries(request.body.entries), validValues);
  assert.ok(app.fields.every(field => field.value === '' && !field.disabled));
  assert.match(app.status.className, /alert-success/);
  assert.deepEqual(app.events, ['quote_sent']);
  assert.equal(app.button.disabled, false);
});

test('invalid forms never call the mail endpoint', async () => {
  const app = setup({ valid: false });
  await app.submit();
  assert.equal(app.calls.length, 0);
  assert.equal(app.status.textContent, '');
});

test('locks edits and ignores repeat submissions while waiting for the mail service', async () => {
  let resolve;
  const pending = new Promise(done => { resolve = done; });
  const app = setup({ fetchResult: () => pending });
  const sending = app.submit();
  assert.ok(app.fields.every(field => field.disabled));
  assert.equal(app.button.disabled, true);
  assert.equal(app.form.attributes['aria-busy'], 'true');
  await app.submit();
  assert.equal(app.calls.length, 1);
  assert.equal(app.events.length, 0);
  resolve({ ok: true, json: async () => ({ sent: true, message: 'Enviado.' }) });
  await sending;
  assert.equal(app.button.disabled, false);
  assert.equal(app.form.attributes['aria-busy'], undefined);
});

for (const failure of ['service', 'network', 'non-json', 'unconfirmed']) {
  test(`${failure} failure preserves fields, restores controls and does not report a conversion`, async () => {
    const app = setup({ fetchResult: async () => {
      if (failure === 'network') throw new Error('Network offline');
      if (failure === 'non-json') return { ok: false, json: async () => { throw new Error('HTML response'); } };
      return { ok: failure === 'unconfirmed', json: async () => ({ sent: false, message: 'No se pudo enviar.', errors: {} }) };
    } });
    await app.submit();
    assert.deepEqual(Object.fromEntries(app.fields.map(field => [field.name, field.value])), validValues);
    assert.equal(app.button.disabled, false);
    assert.equal(app.button.textContent, 'Enviar solicitud por correo');
    assert.match(app.status.className, /alert-danger/);
    assert.equal(app.events.length, 0);
    assert.equal(app.calls.length, 1);
  });
}

test('server field validation is exposed to the browser and clears on editing', async () => {
  const app = setup({ fetchResult: async () => ({ ok: false, json: async () => ({ sent: false, message: 'Revisa tu correo.', errors: { email: 'Correo inválido.' } }) }) });
  await app.submit();
  const email = app.form.elements.namedItem('email');
  assert.equal(email.attributes['aria-invalid'], 'true');
  assert.equal(email.validity, 'Correo inválido.');
  app.handlers.input({ target: email });
  assert.equal(email.validity, '');
  assert.equal(email.attributes['aria-invalid'], undefined);
});

test('missing fetch leaves native PHP submission available', () => {
  assert.equal(setup({ hasFetch: false }).handlers.submit, undefined);
});

test('an unexpected destination never receives data through fetch', () => {
  assert.equal(setup({ action: 'https://example.com/collect' }).handlers.submit, undefined);
});
