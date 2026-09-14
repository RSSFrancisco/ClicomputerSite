<?php
// Cada cámara genera su conexión y su canal en el monitor. No son imágenes reales.
$cameras = [
    ['position' => 'top-left', 'label' => 'Acceso'],
    ['position' => 'top-right', 'label' => 'Exterior'],
    ['position' => 'bottom-left', 'label' => 'Bodega'],
    ['position' => 'bottom-right', 'label' => 'Oficina'],
];
?>
<figure class="security-network" data-network-animation>
  <div class="cctv-header" aria-hidden="true">
    <span>Red de videovigilancia</span>
    <span class="cctv-protocol">CCTV / IP</span>
  </div>
  <div class="cctv-scene" role="img" aria-label="Ilustración de cuatro cámaras conectadas a un monitor y un grabador NVR, con señales que recorren la red">
    <?php foreach ($cameras as $index => $camera): ?>
      <div class="cctv-link cctv-link--<?= e($camera['position']) ?>" style="--delay: -<?= e($index) ?>s" aria-hidden="true">
        <span class="cctv-signal"></span>
      </div>
      <div class="cctv-camera cctv-camera--<?= e($camera['position']) ?>" style="--delay: -<?= e($index) ?>s" aria-hidden="true">
        <div class="cctv-camera-icon">
          <span class="cctv-camera-head"><span class="cctv-camera-lens"></span></span>
        </div>
        <span class="cctv-camera-name"><?= e($camera['label']) ?></span>
      </div>
    <?php endforeach ?>
    <div class="cctv-hub" aria-hidden="true">
      <div class="cctv-monitor">
        <?php foreach ($cameras as $index => $camera): ?>
          <div class="cctv-feed" style="--delay: -<?= e($index) ?>s">
            <span>CAM 0<?= e($index + 1) ?></span>
          </div>
        <?php endforeach ?>
      </div>
      <div class="cctv-recorder"><span>NVR</span><span class="cctv-recorder-lights"></span></div>
    </div>
  </div>
  <figcaption class="cctv-caption">
    <strong>Todo conectado. Todo bajo control.</strong>
    <span>Monitoreo, grabación y acceso remoto.</span>
  </figcaption>
</figure>
