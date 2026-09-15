<section class="search-page section-padding">
  <div class="container">
    <div class="search-page-content">
      <span class="section-badge">Encuentra lo que necesitas</span>
      <h1>Buscar en Clicomputer</h1>
      <form action="/buscar" method="get" role="search" aria-label="Búsqueda en los resultados" class="search-page-form my-4">
        <label for="searchQuery" class="visually-hidden">Qué quieres buscar</label>
        <input type="search" id="searchQuery" name="q" class="form-control" value="<?= e($searchQuery) ?>"
          placeholder="Cámaras, redes, soporte..." minlength="2" maxlength="120" required>
        <button type="submit" class="btn btn-primary-custom">Buscar</button>
      </form>

      <?php if ($searchError): ?>
        <p role="alert"><?= e($searchError) ?></p>
      <?php elseif ($searchQuery === ''): ?>
        <p>Escribe al menos dos caracteres para buscar servicios, proyectos o información de contacto.</p>
      <?php elseif (!$searchResults): ?>
        <p>No encontramos resultados para «<?= e($searchQuery) ?>». Prueba con otro término o con menos palabras.</p>
      <?php else: ?>
        <p><?= count($searchResults) ?> <?= count($searchResults) === 1 ? 'resultado' : 'resultados' ?> para «<?= e($searchQuery) ?>»</p>
        <ol class="search-results list-unstyled">
          <?php foreach ($searchResults as $result): ?>
            <li><?= $view->render('partials/search-result', ['result' => $result]) ?></li>
          <?php endforeach ?>
        </ol>
      <?php endif ?>

      <?php if (!$searchResults): ?>
        <div class="search-examples d-flex flex-wrap gap-2 mt-4" aria-label="Ejemplos de búsqueda">
          <?php foreach (['Cámaras', 'Soporte', 'Redes', 'Instalaciones industriales'] as $example): ?>
            <a class="btn btn-outline-custom btn-sm" href="<?= e('/buscar?q=' . rawurlencode($example)) ?>"><?= e($example) ?></a>
          <?php endforeach ?>
        </div>
      <?php endif ?>
    </div>
  </div>
</section>
