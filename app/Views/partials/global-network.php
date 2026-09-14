<?php
// Elementos decorativos: las posiciones son porcentajes del globo.
$continents = ['north-america', 'south-america', 'greenland', 'europe', 'africa', 'asia'];
$nodes = [[28, 41], [42, 65], [65, 31], [68, 57], [85, 38]];
?>
<figure class="global-network" data-network-animation>
  <div class="network-scene" role="img" aria-label="Planeta Tierra con conexiones que comparten información alrededor del mundo">
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
    <span class="network-label network-label--software" aria-hidden="true">Software</span>
    <span class="network-label network-label--data" aria-hidden="true">Datos</span>
    <span class="network-label network-label--networks" aria-hidden="true">Redes</span>
  </div>
  <figcaption class="network-caption">
    <strong>Tecnología sin fronteras</strong>
    <span>Conectamos ideas, datos y negocios.</span>
  </figcaption>
</figure>
