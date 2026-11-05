(() => {
  'use strict';

  const music = document.querySelector('[data-wedding-music]');
  const audio = document.querySelector('[data-wedding-audio]');
  if (!music || !audio) return;

  const toggle = music.querySelector('[data-music-toggle]');
  const label = music.querySelector('[data-music-label]');
  const status = music.querySelector('[data-music-status]');
  const track = status.textContent;
  let wantsPlayback = true;
  let attempt = 0;

  const showState = (state, text, message) => {
    music.dataset.state = state;
    label.textContent = text;
    status.textContent = message;
    toggle.setAttribute('aria-pressed', String(state === 'playing'));
  };
  const removeGestureListeners = () => {
    document.removeEventListener('pointerdown', onGesture, true);
    document.removeEventListener('keydown', onGesture, true);
  };
  const unavailable = () => {
    wantsPlayback = false;
    attempt++;
    removeGestureListeners();
    toggle.disabled = true;
    showState('unavailable', 'Audio no disponible', 'Intenta recargar la invitación');
  };
  const play = () => {
    wantsPlayback = true;
    const current = ++attempt;
    showState('loading', 'Cargando música…', track);
    const result = audio.play();
    if (result && typeof result.catch === 'function') result.catch(error => {
      if (current !== attempt || !wantsPlayback) return;
      if (error.name === 'NotAllowedError') {
        showState('blocked', 'Activar música', 'Toca para activar el sonido');
      } else if (error.name !== 'AbortError') unavailable();
    });
  };
  function onGesture(event) {
    if (!wantsPlayback || music.dataset.state !== 'blocked') return;
    if (event.target.closest('[data-music-toggle]')) return;
    if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
    play();
  }

  audio.autoplay = true;
  audio.loop = true;
  audio.volume = 0.35;
  audio.controls = false;
  audio.hidden = true;
  music.hidden = false;
  document.body.classList.add('has-wedding-music');

  audio.addEventListener('playing', () => {
    removeGestureListeners();
    showState('playing', 'Pausar música', track + ' · En repetición');
  });
  audio.addEventListener('pause', () => {
    if (wantsPlayback) {
      showState('blocked', 'Activar música', 'Toca para continuar');
      document.addEventListener('pointerdown', onGesture, true);
      document.addEventListener('keydown', onGesture, true);
    } else showState('paused', 'Reproducir música', 'Música en pausa');
  });
  audio.addEventListener('error', unavailable);
  toggle.addEventListener('click', () => {
    if (!audio.paused) {
      wantsPlayback = false;
      attempt++;
      removeGestureListeners();
      audio.pause();
    } else play();
  });

  document.addEventListener('pointerdown', onGesture, true);
  document.addEventListener('keydown', onGesture, true);
  play();
})();
