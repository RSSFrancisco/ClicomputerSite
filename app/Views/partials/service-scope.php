<?php if (isset($page['scope'])): $scope = $page['scope']; ?>
<section class="section-padding-sm scope-section"><div class="container">
  <div class="content-intro"><h2><?= e($scope['heading']) ?></h2><p><?= e($scope['intro']) ?></p></div>
  <div class="row g-4 mb-5">
    <?php foreach ($scope['deliverables'] as [$title, $body]): ?>
      <div class="col-lg-4"><article class="service-card h-100"><h3 class="h4"><?= e($title) ?></h3><p><?= e($body) ?></p></article></div>
    <?php endforeach ?>
  </div>
  <div class="row g-5">
    <div class="col-lg-6"><h2>Qué influye en el precio</h2><p><?= e($scope['budget']) ?></p>
      <h3 class="h4 mt-4">Tiempos, garantía y soporte</h3><p><?= e($scope['delivery']) ?></p></div>
    <div class="col-lg-6"><h2>Prepara tu cotización</h2><ul class="content-checklist">
      <?php foreach ($scope['prepare'] as $item): ?><li><?= e($item) ?></li><?php endforeach ?>
    </ul><a class="btn btn-primary-custom" href="/contacto.html">Solicitar una cotización</a></div>
  </div>
  <div class="content-links mt-4">
    <a href="/<?= e($scope['guide']['file']) ?>"><?= e($scope['guide']['label']) ?></a>
    <?php if (isset($scope['evidence'])): ?><a href="/<?= e($scope['evidence']['file']) ?>"><?= e($scope['evidence']['label']) ?></a><?php endif ?>
  </div>
</div></section>
<?php endif ?>
