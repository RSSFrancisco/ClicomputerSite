<?php
$base = '/eventos/' . $event['slug'];
$icons = [
    'arrow' => '<path d="M5 12h14m-5-5 5 5-5 5"/>',
    'calendar' => '<rect x="4" y="5" width="16" height="16" rx="2"/><path d="M16 3v4M8 3v4M4 11h16m-11 5h2"/>',
    'church' => '<path d="M12 2v6m-3-3h6M5 21V11l7-5 7 5v10M2 21h20M9 21v-6h6v6M2 21V12h3m14 0h3v9"/>',
    'celebration' => '<path d="m4 3 8 8 8-8M6 3h12M12 11v9m-4 1h8M5 3v3m14-3v3"/>',
    'pin' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
];
$icon = static fn (string $name): string => '<svg class="event-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[$name] . '</svg>';
?>
<header class="event-header">
  <a class="couple-mark" href="#inicio" aria-label="Anselmo y Dulce, inicio">A<span>&</span>D</a>
  <nav class="wedding-nav" aria-label="Nuestra boda">
    <a href="#nuestro-dia">Nuestro día</a>
    <a href="#celebracion">La celebración</a>
    <a class="nav-date" href="<?= e($base) ?>/calendario.ics" rel="nofollow" download><?= $icon('calendar') ?><span>Guardar la fecha</span></a>
  </nav>
</header>
<main id="contenido">
  <section class="wedding-hero" id="inicio" aria-labelledby="wedding-title">
    <div class="hero-copy">
      <p class="eyebrow hero-intro"><span class="short-line"></span>CON LA BENDICIÓN DE DIOS</p>
      <p class="hero-kicker">Nos casamos</p>
      <h1 id="wedding-title"><span><?= e($event['names'][0]) ?></span><span class="name-and">&</span><span><?= e($event['names'][1]) ?></span></h1>
      <p class="hero-message">El mejor capítulo de nuestra historia<br>comienza contigo a nuestro lado.</p>
      <div class="hero-date"><span>SÁBADO</span><time datetime="<?= e($event['date']) ?>">28 <span>NOVIEMBRE</span> 2026</time></div>
      <a class="event-button" href="#celebracion">Acompáñanos <?= $icon('arrow') ?></a>
    </div>
    <div class="hero-art">
      <span class="art-caption">UNA INVITACIÓN PARA TI</span>
      <div class="invitation-surround">
        <span class="frame-corner frame-corner-top" aria-hidden="true"></span>
        <a class="invitation-link" href="<?= e($base) ?>/invitacion.png" rel="nofollow" data-invitation-open aria-label="Ampliar la invitación de Anselmo y Dulce">
          <img class="invitation-image" src="<?= e($base) ?>/invitacion.png" width="1115" height="1599" fetchpriority="high" alt="Invitación original de nuestra boda, con rosas rojas y detalles dorados. Anselmo y Dulce, 28 de noviembre de 2026.">
          <span class="invitation-hint">Ver invitación <span aria-hidden="true">↗</span></span>
        </a>
        <span class="frame-corner frame-corner-bottom" aria-hidden="true"></span>
      </div>
      <span class="wax-seal" aria-hidden="true">A<span>&</span>D</span>
      <span class="art-side-note" aria-hidden="true">EL COMIENZO DE NUESTRO SIEMPRE</span>
    </div>
    <a class="scroll-cue" href="#nuestro-dia"><span class="scroll-line" aria-hidden="true"></span>LA CUENTA REGRESIVA HA COMENZADO</a>
    <span class="hero-folio" aria-hidden="true">01 / NUESTRA BODA</span>
  </section>

  <section class="countdown-section" id="nuestro-dia" aria-labelledby="countdown-title">
    <div class="countdown-heading" data-reveal>
      <p class="eyebrow">CADA VEZ MÁS CERCA</p>
      <h2 id="countdown-title">Un día. <em>Toda una vida.</em></h2>
      <p id="countdown-message">Falta muy poco para celebrar nuestro amor.</p>
    </div>
    <div class="countdown" data-countdown="<?= e($event['start']) ?>" aria-label="Tiempo hasta la ceremonia" data-reveal>
      <div><span data-count="days">—</span><span class="count-label">DÍAS</span></div>
      <span class="count-divider" aria-hidden="true">:</span>
      <div><span data-count="hours">—</span><span class="count-label">HORAS</span></div>
      <span class="count-divider" aria-hidden="true">:</span>
      <div><span data-count="minutes">—</span><span class="count-label">MINUTOS</span></div>
      <span class="count-divider" aria-hidden="true">:</span>
      <div><span data-count="seconds">—</span><span class="count-label">SEGUNDOS</span></div>
    </div>
    <noscript><p class="countdown-fallback">Te esperamos el <?= e($event['date_display']) ?> a las 4:00 p. m.</p></noscript>
    <a class="text-link" href="<?= e($base) ?>/calendario.ics" download rel="nofollow"><?= $icon('calendar') ?> Aparta este día en tu calendario <?= $icon('arrow') ?></a>
  </section>

  <section class="celebration-section" id="celebracion" aria-labelledby="celebration-title">
    <div class="section-heading" data-reveal>
      <div><p class="eyebrow">DOS MOMENTOS, UN MISMO AMOR</p><h2 id="celebration-title">Nos vemos <em>en el altar.</em></h2></div>
      <p>Después del «sí, acepto»,<br>sigamos celebrando juntos.</p>
    </div>
    <div class="celebration-grid">
      <?php foreach (['ceremony' => ['01', 'church', 'MISA'], 'reception' => ['02', 'celebration', 'RECEPCIÓN']] as $key => [$number, $symbol, $label]): $place = $event[$key]; ?>
      <article class="venue venue-<?= e($key) ?>" data-reveal>
        <div class="venue-top"><?= $icon($symbol) ?><span><?= e($number) ?></span></div>
        <p class="eyebrow"><?= e($label) ?></p>
        <h3><?= e($place['title']) ?></h3>
        <time class="venue-time" datetime="<?= e($place['start']) ?>"><?= e($place['time']) ?><?= $key === 'reception' ? '<span>Te esperamos puntual</span>' : '' ?></time>
        <div class="venue-location"><h4><?= e($place['venue']) ?></h4><p><?= e($place['address']) ?><?= isset($place['reference']) ? '<br>' . e($place['reference']) : '' ?></p></div>
        <a class="venue-map" href="<?= e('https://www.google.com/maps/search/?api=1&query=' . rawurlencode($place['venue'] . ', ' . $place['address'])) ?>" target="_blank" rel="nofollow noopener noreferrer"><?= $icon('pin') ?> Buscar en Google Maps <span aria-hidden="true">↗</span></a>
      </article>
      <?php endforeach ?>
    </div>
  </section>

  <section class="wedding-closing" aria-labelledby="closing-title">
    <div class="closing-rings" aria-hidden="true"><span></span><span></span></div>
    <p class="eyebrow" data-reveal">CON MUCHO AMOR</p>
    <h2 id="closing-title" data-reveal>Hay momentos que son mejores<br><em>cuando los compartimos.</em></h2>
    <p data-reveal>Gracias por ser parte del nuestro.</p>
    <span class="closing-signature" data-reveal>Anselmo & Dulce</span>
    <a class="closing-download" href="<?= e($base) ?>/invitacion.png" download="invitacion-anselmo-y-dulce.png" rel="nofollow">Descargar invitación <span aria-hidden="true">↓</span></a>
  </section>
</main>
<footer class="event-footer"><a href="/eventos" rel="nofollow">Eventos <span aria-hidden="true">↗</span></a><span>ANSELMO & DULCE <span class="footer-dot">·</span> 28.11.2026</span><a href="#inicio">Volver arriba ↑</a></footer>
<?php if (!empty($event['music'])): ?>
<?php $musicUrl = $base . '/musica.mp3?v=' . substr(hash_file('sha256', ROOT_PATH . '/data/events/' . $event['slug'] . '/' . $event['music']['file']), 0, 12); ?>
<audio class="wedding-audio" id="wedding-song" data-wedding-audio src="<?= e($musicUrl) ?>" autoplay loop controls preload="auto" aria-label="<?= e($event['music']['label'] . ' · ' . $event['music']['artist']) ?>"></audio>
<aside class="wedding-music" data-wedding-music aria-label="Música de la invitación" hidden>
  <button type="button" class="music-toggle" data-music-toggle aria-pressed="false" title="<?= e($event['music']['label'] . ' · ' . $event['music']['artist']) ?>">
    <span class="music-note" aria-hidden="true">♫</span>
    <span data-music-label>Activar música</span>
  </button>
  <span class="music-status" data-music-status role="status"><?= e($event['music']['label']) ?></span>
</aside>
<?php endif ?>
<dialog class="invitation-dialog" aria-labelledby="invitation-dialog-title" data-invitation-dialog>
  <div class="dialog-toolbar"><h2 id="invitation-dialog-title">Nuestra invitación</h2><button type="button" data-invitation-close aria-label="Cerrar invitación">Cerrar <span aria-hidden="true">×</span></button></div>
  <img src="<?= e($base) ?>/invitacion.png" width="1115" height="1599" alt="Invitación original. Boda de Anselmo y Dulce, el 28 de noviembre de 2026. Misa a las 4:00 p. m. en la Parroquia San Rafael Guizar y Valencia, Santa Leticia. Recepción a las 5:30 p. m. en Salón el Campesino, La Luz, Francisco I. Madero, junto al Campo Deportivo.">
</dialog>
