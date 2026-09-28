    <?php if ($editorScreen === 'dashboard'): ?>
      <div class="editor-toolbar"><p class="mb-0">Escribe sobre tecnología y lo que sucede en Veracruz.</p><a class="btn btn-primary-custom" href="/admin/noticias/nueva"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva noticia</a></div>
      <div class="editor-panel">
        <?php if (!$editorPosts): ?><h2 class="h4">Tu primera noticia empieza aquí</h2><p>Agrega un título, escribe el contenido y elige una foto. Puedes guardarla como borrador antes de publicarla.</p><?php endif ?>
        <?php foreach ($editorPosts as $post): ?><?= $view->render('partials/blog-editor-row', compact('post')) ?><?php endforeach ?>
      </div>
    <?php elseif ($editorScreen === 'editor'): ?>
      <?= $view->render('partials/blog-editor-form', compact('editorValues', 'editorErrors', 'editorToken')) ?>
    <?php elseif ($editorScreen === 'preview'): ?>
      <p class="alert alert-info">Vista previa privada. Esta página no publica ni cambia el estado de la noticia.</p>
      <a class="btn btn-outline-custom mb-4" href="/admin/noticias/editar?id=<?= (int) $editorValues['id'] ?>">Volver al editor</a>
      <article class="editor-panel"><header class="news-header"><p class="brand-eyebrow"><?= e(App\Models\Blog::CATEGORIES[$editorValues['category']]) ?></p><h2><?= e($editorValues['title']) ?></h2><p><?= e($editorValues['excerpt']) ?></p></header>
        <?php if ($editorValues['image_id']): ?><img class="editor-photo" src="/admin/noticias/imagen/<?= e($editorValues['image_id']) ?>" alt="<?= e($editorValues['image_alt']) ?>"><?php endif ?>
        <div class="news-body"><?= $view->render('partials/news-body', ['post' => $editorValues]) ?></div>
      </article>
    <?php else: ?><a href="/admin/noticias">Volver al panel de noticias</a><?php endif ?>

