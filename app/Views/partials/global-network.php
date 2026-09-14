<?php
// Elementos decorativos: las posiciones son porcentajes del globo.
$continents = ['north-america', 'south-america', 'greenland', 'europe', 'africa', 'asia'];
$nodes = [[28, 41], [42, 65], [65, 31], [68, 57], [85, 38]];
?>
<figure class="global-network" data-network-animation>
  <div class="network-scene">
    <div class="network-artwork" role="img" aria-label="Planeta Tierra con conexiones que comparten información alrededor del mundo">
      <div class="network-globe" aria-hidden="true">
        <?php foreach ($continents as $continent): ?>
          <span class="globe-land globe-land--<?= e($continent) ?>"></span>
        <?php endforeach ?>
        <span class="globe-grid globe-grid--meridian"></span>
        <span class="globe-grid globe-grid--meridian-small"></span>
        <span class="globe-grid globe-grid--latitude"></span>
        <span class="globe-grid globe-grid--latitude-south"></span>
        <?php foreach ($nodes as $index => [$x, $y]): ?>
          <span class="network-node" style="left: <?= e($x) ?>%; top: <?= e($y) ?>%; --delay: -<?= e($index) ?>s"></span>
        <?php endforeach ?>
      </div>
      <?php foreach ([-28, 30, 82] as $index => $tilt): ?>
        <div class="network-orbit" style="--tilt: <?= e($tilt) ?>deg; --duration: <?= e(8 + $index * 3) ?>s; --delay: -<?= e($index * 3) ?>s" aria-hidden="true">
          <span class="network-packet"></span>
        </div>
      <?php endforeach ?>
    </div>
    <div class="network-services" role="group" aria-label="Conoce nuestros servicios">
      <?php foreach ($services as $service): ?>
        <button type="button" class="btn network-label network-label--<?= e($service['id']) ?>"
          data-service-popover title="<?= e($service['description']) ?>">
          <i class="bi <?= e($service['icon']) ?>" aria-hidden="true"></i>
          <span class="network-label-text"><?= e($service['label']) ?></span>
        </button>
      <?php endforeach ?>
    </div>
  </div>
  <figcaption class="network-caption">
    <strong>Tecnología sin fronteras</strong>
    <span>Conectamos ideas, datos y negocios.</span>
  </figcaption>
</figure>
