/** GA4 opcional. Solo eventos permitidos y URLs canónicas, nunca valores del formulario. */
(() => {
  'use strict';
  const configElement = document.getElementById('analyticsConfig');
  if (!configElement) return;
  let config;
  try { config = JSON.parse(configElement.textContent); } catch (_) { return; }
  if (!/^G-[A-Z0-9]{4,20}$/.test(config.id || '')) return;
  const panel = document.getElementById('analyticsConsent');
  const settings = document.getElementById('analyticsSettings');
  const accept = document.getElementById('analyticsAccept');
  const decline = document.getElementById('analyticsDecline');
  if (!panel || !settings || !accept || !decline) return;
  let allowed = false;
  let initialized = false;
  const key = 'cli-analytics-choice-v1';
  const fields = { page_location: config.page, page_title: config.title, page_referrer: '' };
  try {
    const referrer = new URL(document.referrer);
    // Conservar la procedencia sin enviar rutas, búsquedas ni fragmentos externos.
    if (['https:', 'http:'].includes(referrer.protocol)) fields.page_referrer = referrer.origin + '/';
  } catch (_) { /* Una visita directa no tiene referrer. */ }

  function track(name, method) {
    if (!allowed || !['contact_click', 'quote_prepared'].includes(name)) return;
    const parameters = { ...fields };
    if (name === 'contact_click') {
      if (!['whatsapp', 'phone', 'email'].includes(method)) return;
      parameters.contact_method = method;
    }
    window.gtag('event', name, parameters);
  }

  function enable() {
    allowed = true;
    window['ga-disable-' + config.id] = false;
    if (initialized) return;
    initialized = true;
    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', config.id, { ...fields, send_page_view: false,
      allow_google_signals: false, allow_ad_personalization_signals: false });
    window.gtag('event', 'page_view', fields);
    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(config.id);
    document.head.appendChild(script);
  }

  function choose(value) {
    try { localStorage.setItem(key, value); } catch (_) { /* La elección funciona durante esta visita. */ }
    panel.hidden = true;
    if (value === 'allowed') enable();
    else { allowed = false; window['ga-disable-' + config.id] = true; }
    settings.focus();
  }

  window.CliAnalytics = { track };
  settings.hidden = false;
  settings.addEventListener('click', () => { panel.hidden = false; decline.focus(); });
  accept.addEventListener('click', () => choose('allowed'));
  decline.addEventListener('click', () => choose('declined'));
  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link) return;
    const href = link.getAttribute('href');
    if (/^https:\/\/wa\.me\/\d{10,15}(?:\?|$)/.test(href)) track('contact_click', 'whatsapp');
    else if (href.startsWith('tel:')) track('contact_click', 'phone');
    else if (href.startsWith('mailto:')) track('contact_click', 'email');
  });
  let choice;
  try { choice = localStorage.getItem(key); } catch (_) { /* Preguntar sin persistir. */ }
  if (choice === 'allowed') enable();
  else if (choice !== 'declined') panel.hidden = false;
})();
