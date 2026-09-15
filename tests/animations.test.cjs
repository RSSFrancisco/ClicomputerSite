const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

// Entorno mínimo para comprobar el scroll y las APIs antiguas de Android/WebView.
function browser({ width = 390, reduced = false, legacy = false, footerHeight = 400, cardRight = width - 12 } = {}) {
  const events = {};
  const frames = new Map();
  let nextFrame = 0;
  let mediaChange;
  let intersection;
  const element = () => ({ classes: new Set(), style: {} });
  const main = element(), scene = element(), globe = element(), rocket = element(), network = element();
  let borderReads = 0;
  main.querySelector = () => ({ getBoundingClientRect: () => { borderReads++; return { right: cardRight }; } });
  const sparks = Array.from({ length: 6 }, element);
  const document = { hidden: false, documentElement: { scrollHeight: 7500 + footerHeight } };
  const media = { matches: reduced };
  media[legacy ? 'addListener' : 'addEventListener'] = (...args) => { mediaChange = args.at(-1); };
  const window = {
    innerWidth: width, innerHeight: 844, scrollY: 0,
    matchMedia: () => media,
    addEventListener: (name, callback) => { events[name] = callback; },
    requestAnimationFrame: callback => { frames.set(++nextFrame, callback); return nextFrame; },
    cancelAnimationFrame: id => frames.delete(id),
  };
  main.getBoundingClientRect = () => ({ top: -window.scrollY, left: 0, width, height: 7500 });
  globe.getBoundingClientRect = () => ({ top: 635 - window.scrollY, left: width / 2 - 117, width: 234, height: 234 });
  function $(value) {
    if (typeof value === 'function') return value();
    const selectors = { '[data-space-journey]': scene, '.network-globe': globe, '[data-network-animation]': network };
    const items = typeof value === 'string' ? [selectors[value]].filter(Boolean) : Array.isArray(value) ? value : [value];
    const api = {
      length: items.length, 0: items[0],
      parent: () => $(main),
      find: selector => $(selector === '.space-rocket' ? rocket : sparks),
      toArray: () => items,
      each: callback => { items.forEach(item => callback.call(item)); return api; },
      css: styles => { items.forEach(item => Object.assign(item.style, styles)); return api; },
      toggleClass: (name, enabled) => { items.forEach(item => enabled ? item.classes.add(name) : item.classes.delete(name)); return api; },
      removeClass: name => { items.forEach(item => item.classes.delete(name)); return api; },
      on: (names, callback) => { names.split(' ').forEach(name => { events[name.split('.')[0]] = callback; }); return api; },
    };
    return api;
  }
  class IntersectionObserver {
    constructor(callback) { intersection = callback; }
    observe() {}
  }
  window.jQuery = $;
  window.IntersectionObserver = IntersectionObserver;
  return {
    window, document, media, scene, rocket, main, network, sparks, frames,
    load(file) {
      vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../js/components', file), 'utf8'), { window, document, IntersectionObserver });
    },
    flush() { const callbacks = [...frames.values()]; frames.clear(); callbacks.forEach(callback => callback()); },
    scroll(y) { window.scrollY = y; events.scroll(); this.flush(); },
    motion(value) { media.matches = value; mediaChange(); this.flush(); },
    hidden(value) { document.hidden = value; events.visibilitychange(); this.flush(); },
    intersect(value) { intersection([{ isIntersecting: value }]); },
    get borderReads() { return borderReads; },
  };
}

for (const legacy of [false, true]) {
  const b = browser({ legacy });
  b.load('space-journey.js');
  b.flush();
  assert(b.scene.classes.has('is-enabled'), 'El cohete se inicializa con ambas APIs de MediaQueryList.');
  b.scroll(1600);
  assert(b.scene.classes.has('is-flying'));
  const [x, y] = b.rocket.style.transform.match(/translate3d\(([^p]+)px, ([^p]+)px/).slice(1).map(Number);
  assert(x > 356 && x < 374, 'El cohete sigue el borde derecho con una superposición pequeña.');
  assert.equal(b.borderReads, 1, 'El scroll reutiliza las medidas sin releer el borde de la tarjeta.');
  assert(y - b.window.scrollY > 0 && y - b.window.scrollY < b.window.innerHeight, 'El cohete permanece en pantalla al bajar.');
  const forward = b.rocket.style.transform;
  b.scroll(2200);
  b.scroll(1600);
  assert.equal(b.rocket.style.transform, forward, 'La trayectoria se invierte sin saltos.');
  b.hidden(true);
  assert(!b.scene.classes.has('is-enabled'));
  b.hidden(false);
  assert(b.scene.classes.has('is-flying'));
  b.motion(true);
  assert(b.scene.classes.has('is-enabled') && !b.scene.classes.has('is-flying'));
  assert(b.rocket.style.opacity > 0, 'Movimiento reducido muestra un cohete estático.');
  assert(b.sparks.every(spark => spark.style.opacity === 0));
  const resting = b.rocket.style.transform;
  b.scroll(2500);
  assert.equal(b.rocket.style.transform, resting);
  assert.equal(b.frames.size, 0);
  b.motion(false);
  assert(b.scene.classes.has('is-flying'));

  const n = browser({ legacy });
  n.load('network-animation.js');
  n.intersect(true);
  assert(n.network.classes.has('is-running'), 'Tierra/CCTV arrancan también con addListener.');
  n.intersect(false);
  assert(!n.network.classes.has('is-running'));
  n.intersect(true);
  n.hidden(true);
  assert(!n.network.classes.has('is-running'));
  n.hidden(false);
  assert(n.network.classes.has('is-running'));
  n.motion(true);
  assert(!n.network.classes.has('is-running'));
}

const resting = browser({ reduced: true, legacy: true });
resting.load('space-journey.js');
resting.flush();
assert(resting.scene.classes.has('is-enabled') && resting.rocket.style.opacity > 0, 'El cohete aparece también al cargar con movimiento reducido.');
assert(!resting.scene.classes.has('is-flying'));

// El contenedor Bootstrap de 540 px se centra en pantallas móviles más anchas.
for (const [width, cardRight] of [[320, 308], [390, 378], [600, 558], [767, 641.5]]) {
  const mobile = browser({ width, cardRight });
  mobile.load('space-journey.js');
  mobile.flush();
  const xs = [];
  for (let scroll = 1600; scroll <= 6000; scroll += 200) {
    mobile.scroll(scroll);
    const x = Number(mobile.rocket.style.transform.match(/translate3d\(([^p]+)px/)[1]);
    xs.push(x);
    assert(x + 17 < width && x - 17 > 0, 'El cohete completo se mantiene dentro de la pantalla.');
    assert(Math.abs(x - cardRight) < 25, 'La curva sigue la tarjeta, no el extremo de la pantalla.');
  }
  assert(Math.max(...xs) - Math.min(...xs) > 10, 'El recorrido conserva el movimiento serpenteante.');
  assert.equal(mobile.borderReads, 1, 'La trayectoria completa comparte una sola medición del borde.');
}

const tallFooter = browser({ width: 320, footerHeight: 2200 });
tallFooter.load('space-journey.js');
tallFooter.flush();
tallFooter.scroll(6000);
const rocketY = Number(tallFooter.rocket.style.transform.match(/translate3d\([^,]+, ([^p]+)px/)[1]) - tallFooter.window.scrollY;
assert(rocketY > 0 && rocketY < tallFooter.window.innerHeight, 'Un footer alto no saca al cohete de pantalla antes de Contacto.');

console.log('OK: animaciones con API actual/antigua, scroll móvil, retorno, visibilidad y movimiento reducido.');
