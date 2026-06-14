/**
 * Clicomputer México — View Loader
 * Loads HTML partials via fetch() or falls back to inline templates for file:// protocol
 */
const ViewLoader = (() => {
  'use strict';
  const cache = {};
  const BASE_PATH = 'views/';
  const isFileProtocol = window.location.protocol === 'file:';

  async function load(viewName, targetSelector) {
    const target = document.querySelector(targetSelector);
    if (!target) return false;

    // Try inline template first (file:// fallback)
    const tpl = document.getElementById('tpl-' + viewName.replace('partials/', 'partial-'));
    if (tpl) {
      target.innerHTML = tpl.innerHTML;
      return true;
    }

    if (isFileProtocol) return false;

    const url = BASE_PATH + viewName + '.html';
    try {
      let html = cache[url];
      if (!html) {
        const r = await fetch(url);
        if (!r.ok) throw new Error('HTTP ' + r.status);
        html = cache[url] = await r.text();
      }
      target.innerHTML = html;
      return true;
    } catch (e) {
      console.error('ViewLoader:', e);
      return false;
    }
  }

  async function loadPartial(name, sel) {
    return load('partials/' + name, sel);
  }

  return { load, loadPartial };
})();
