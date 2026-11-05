const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function setup({ playError = null, missingAudio = false } = {}) {
  const element = () => ({
    dataset: {}, attributes: {}, listeners: new Map(), hidden: false, textContent: '',
    addEventListener(name, listener) {
      if (!this.listeners.has(name)) this.listeners.set(name, new Set());
      this.listeners.get(name).add(listener);
    },
    removeEventListener(name, listener) { this.listeners.get(name)?.delete(listener); },
    setAttribute(name, value) { this.attributes[name] = value; },
    emit(name, event = {}) {
      const detail = { type: name, target: { closest: () => null }, ...event };
      [...(this.listeners.get(name) || [])].forEach(listener => listener(detail));
    },
  });
  const music = element(), toggle = element(), label = element(), status = element();
  const audio = Object.assign(element(), {
    paused: true, controls: true, playCalls: 0, pauseCalls: 0, nextError: playError,
    play() {
      this.playCalls++;
      if (this.nextError) {
        const error = this.nextError;
        this.nextError = null;
        return Promise.reject(error);
      }
      this.paused = false;
      return Promise.resolve();
    },
    pause() { this.pauseCalls++; this.paused = true; this.emit('pause'); },
  });
  status.textContent = 'Symphony';
  music.hidden = true;
  music.querySelector = selector => ({
    '[data-music-toggle]': toggle,
    '[data-music-label]': label,
    '[data-music-status]': status,
  })[selector];
  const document = Object.assign(element(), {
    body: { classList: { add() {} } },
    querySelector: selector => selector === '[data-wedding-music]' ? music : missingAudio ? null : audio,
  });
  vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../js/components/wedding-music.js'), 'utf8'), { document });
  return { music, toggle, label, status, audio, document };
}

test('plays the local MP3 automatically in a loop without restarting on scroll', () => {
  const page = setup();
  assert.equal(page.audio.autoplay, true);
  assert.equal(page.audio.loop, true);
  assert.equal(page.audio.volume, 0.35);
  assert.equal(page.audio.controls, false);
  assert.equal(page.audio.hidden, true);
  assert.equal(page.music.hidden, false);
  assert.equal(page.audio.playCalls, 1);
  assert.equal(page.toggle.attributes['aria-pressed'], 'false');
  page.audio.emit('playing');
  assert.equal(page.music.dataset.state, 'playing');
  assert.equal(page.label.textContent, 'Pausar música');
  assert.equal(page.toggle.attributes['aria-pressed'], 'true');
  page.document.emit('scroll');
  page.document.emit('visibilitychange');
  page.document.emit('pointerdown');
  assert.equal(page.audio.playCalls, 1);
  assert.equal(page.audio.pauseCalls, 0);
  assert.equal(page.audio.paused, false);
});

test('blocked autoplay starts on the first gesture and never overrides an explicit pause', async () => {
  const page = setup({ playError: { name: 'NotAllowedError' } });
  await Promise.resolve();
  assert.equal(page.music.dataset.state, 'blocked');
  assert.equal(page.label.textContent, 'Activar música');
  page.document.emit('pointerdown');
  assert.equal(page.audio.playCalls, 2);
  page.audio.emit('playing');
  page.toggle.emit('click');
  assert.equal(page.audio.pauseCalls, 1);
  assert.equal(page.music.dataset.state, 'paused');
  page.document.emit('pointerdown');
  page.document.emit('keydown', { key: 'Enter' });
  assert.equal(page.audio.playCalls, 2, 'Other interactions must respect a voluntary pause');
  page.toggle.emit('click');
  assert.equal(page.audio.playCalls, 3);
  page.audio.emit('playing');
  assert.equal(page.toggle.attributes['aria-pressed'], 'true');
});

test('the music button activates once and ignores its own pointer event', async () => {
  const page = setup({ playError: { name: 'NotAllowedError' } });
  await Promise.resolve();
  page.document.emit('pointerdown', { target: { closest: () => page.toggle } });
  assert.equal(page.audio.playCalls, 1);
  page.toggle.emit('click');
  assert.equal(page.audio.playCalls, 2);
  page.audio.emit('playing');
  assert.equal(page.label.textContent, 'Pausar música');
});

test('unsupported audio fails cleanly and absent audio does not initialize a player', async () => {
  const page = setup({ playError: { name: 'NotSupportedError' } });
  await Promise.resolve();
  assert.equal(page.music.dataset.state, 'unavailable');
  assert.equal(page.toggle.disabled, true);
  assert(!page.status.textContent.toLowerCase().includes('youtube'));
  const missing = setup({ missingAudio: true });
  assert.equal(missing.audio.playCalls, 0);
  assert.equal(missing.music.hidden, true);
});
