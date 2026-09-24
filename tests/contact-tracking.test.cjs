const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function setup(choice, id = 'G-TEST1234') {
  const nodes = {};
  for (const name of ['analyticsConsent', 'analyticsSettings', 'analyticsAccept', 'analyticsDecline']) {
    nodes[name] = { hidden: true, handlers: {}, focus() {}, addEventListener(name, callback) { this.handlers[name] = callback; } };
  }
  nodes.analyticsConfig = { textContent: JSON.stringify({ id, page: 'https://www.clcomputer.com/contacto.html', title: 'Contacto' }) };
  const scripts = [], handlers = {}, saved = {};
  const window = {};
  vm.runInNewContext(fs.readFileSync('js/components/contact-tracking.js', 'utf8'), {
    window,
    document: { getElementById: id => nodes[id], createElement: () => ({}),
      head: { appendChild: element => scripts.push(element) }, addEventListener: (name, cb) => { handlers[name] = cb; } },
    localStorage: { getItem: () => choice, setItem: (key, value) => { saved[key] = value; } }
  });
  const click = href => handlers.click({ target: { closest: () => ({ getAttribute: () => href }) } });
  return { window, nodes, scripts, saved, click };
}

test('Google is never loaded before an explicit choice or after rejection', () => {
  const pending = setup();
  assert.equal(pending.scripts.length, 0);
  assert.equal(pending.nodes.analyticsConsent.hidden, false);
  pending.click('https://wa.me/526567514187?text=Private');
  assert.equal(pending.window.dataLayer, undefined);
  const declined = setup('declined');
  assert.equal(declined.scripts.length, 0);
  assert.equal(declined.nodes.analyticsConsent.hidden, true);
});

test('accepted contact events exclude draft text, mail addresses and arbitrary parameters', () => {
  const app = setup('allowed');
  assert.equal(app.scripts.length, 1);
  app.click('https://wa.me/526567514187?text=PRIVATE_NAME_AND_MESSAGE');
  app.click('mailto:private@example.com?subject=PRIVATE_SUBJECT');
  app.click('tel:+526567514187');
  app.window.CliAnalytics.track('quote_sent', { name: 'PRIVATE_NAME' });
  app.window.CliAnalytics.track('quote_prepared');
  app.window.CliAnalytics.track('generate_lead', 'whatsapp');
  app.window.CliAnalytics.track('contact_click', 'INVALID');
  const events = Array.from(app.window.dataLayer).filter(args => args[0] === 'event');
  assert.deepEqual(events.map(args => args[1]), ['page_view', 'contact_click', 'contact_click', 'contact_click', 'quote_sent']);
  assert.deepEqual(events.slice(1, 4).map(args => args[2].contact_method), ['whatsapp', 'email', 'phone']);
  assert.equal(JSON.stringify(app.window.dataLayer).includes('PRIVATE'), false);
  assert.equal(events.every(args => args[2].page_location === 'https://www.clcomputer.com/contacto.html'), true);
});

test('changing the choice stops collection and enabling again does not inject a second tag', () => {
  const app = setup('allowed');
  app.nodes.analyticsDecline.handlers.click();
  const size = app.window.dataLayer.length;
  app.click('tel:+526567514187');
  assert.equal(app.window.dataLayer.length, size);
  assert.equal(app.window['ga-disable-G-TEST1234'], true);
  app.nodes.analyticsAccept.handlers.click();
  app.click('tel:+526567514187');
  assert.equal(app.scripts.length, 1);
  assert.equal(app.window.dataLayer.length, size + 1);
});

test('an invalid property never loads the Google tag', () => {
  assert.equal(setup('allowed', 'G-1234<script>').scripts.length, 0);
});
