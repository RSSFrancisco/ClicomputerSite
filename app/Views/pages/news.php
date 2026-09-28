<section class="section-padding-sm news-page"><div class="container">
  <header class="news-header">
    <p class="brand-eyebrow">El blog de Clicomputer</p>
    <h1>Tecnología y nuestra región</h1>
    <p>Novedades del mundo digital, ideas útiles y lo que sucede en Veracruz.</p>
  </header>
  <nav class="news-categories" aria-label="Categorías de noticias">
    <?php foreach (['' => 'Todas las noticias'] + App\Models\Blog::CATEGORIES as $key => $label): ?>
      <a href="/noticias.html<?= $key ? '?categoria=' . e($key) : '' ?>" <?= $newsCategory === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach ?>
  </nav>
  <?php if ($newsUnavailable): ?>
    <div class="news-empty"><h2>Volvemos en un momento</h2><p>No pudimos cargar las noticias. Vuelve a intentarlo más tarde.</p></div>
  <?php elseif (!$newsPosts): ?>
    <div class="news-empty"><i class="bi bi-newspaper" aria-hidden="true"></i><h2><?= $newsCategory ? 'Pronto habrá noticias de ' . e(App\Models\Blog::CATEGORIES[$newsCategory]) : 'Un nuevo espacio para estar al día' ?></h2><p>Estamos preparando las primeras publicaciones. Aquí encontrarás tecnología, comunidad y actualidad de Veracruz.</p></div>
  <?php else: ?>
    <div class="news-grid"><?php foreach ($newsPosts as $post): ?><?= $view->render('partials/news-card', compact('post')) ?><?php endforeach ?></div>
  <?php endif ?>
  <?php if ($newsPageCount > 1): ?><nav class="news-pagination" aria-label="Páginas de noticias">
    <?php for ($number = 1; $number <= $newsPageCount; $number++): ?><a href="/noticias.html?<?= e(http_build_query(['categoria' => $newsCategory, 'pagina' => $number])) ?>" <?= $number === $newsPage ? 'aria-current="page"' : '' ?>><?= $number ?></a><?php endfor ?>
  </nav><?php endif ?>
  <aside class="news-guides"><p>¿Buscas ayuda para tu próximo proyecto?</p><a href="/guias.html">Consulta nuestras guías de tecnología <i class="bi bi-arrow-right" aria-hidden="true"></i></a></aside>
</div></section>
