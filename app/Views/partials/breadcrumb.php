<nav class="container page-breadcrumb" aria-label="Ruta de navegación">
  <ol><li><a href="/">Inicio</a></li>
    <?php if (isset($page['parent'])): ?><li><a href="/<?= e($page['parent']['file']) ?>"><?= e($page['parent']['label']) ?></a></li><?php endif ?>
    <li aria-current="page"><?= e($page['label']) ?></li></ol>
</nav>
