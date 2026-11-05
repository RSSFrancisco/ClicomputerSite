<header class="event-header events-header">
  <a class="events-brand" href="/" rel="nofollow">Clicomputer<span>Momentos especiales</span></a>
  <a class="event-back" href="/" rel="nofollow">Volver al sitio <i class="fa-solid fa-arrow-left event-arrow" aria-hidden="true"></i></a>
</header>
<main id="contenido" class="events-index">
  <div class="eyebrow">PARA COMPARTIR Y CELEBRAR</div>
  <h1>Eventos<span class="gold-dot">.</span></h1>
  <p class="events-intro">Hay días que se convierten en recuerdos para siempre.<br>Este es el comienzo de uno de ellos.</p>
  <?php foreach ($events as $item): ?>
  <a class="event-entry" href="/eventos/<?= e($item['slug']) ?>" rel="nofollow">
    <div class="event-entry-image"><img src="/eventos/<?= e($item['slug']) ?>/invitacion.png" width="1115" height="1599" alt="Invitación con rosas rojas y detalles dorados para la boda de Anselmo y Dulce"></div>
    <div class="event-entry-content">
      <span class="eyebrow">NUESTRA BODA</span>
      <h2><?= e(implode(' & ', $item['names'])) ?></h2>
      <p><?= e($item['date_display']) ?></p>
      <span class="entry-action">Abrir invitación <i class="fa-solid fa-arrow-right event-arrow" aria-hidden="true"></i></span>
    </div>
  </a>
  <?php endforeach ?>
</main>
<footer class="event-footer"><span>Hecho para celebrar.</span><a href="/" rel="nofollow">Clicomputer</a></footer>
