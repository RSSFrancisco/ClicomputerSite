<form class="editor-panel editor-form" method="post" action="/admin/noticias/guardar" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= e($editorToken) ?>">
  <input type="hidden" name="id" value="<?= (int) $editorValues['id'] ?>">
  <input type="hidden" name="revision" value="<?= (int) $editorValues['revision'] ?>">
  <p>Los campos con * son obligatorios. La foto y la fuente son opcionales.</p>
  <?php foreach ([
      ['title', 'Título', 180, true, false, ''],
      ['excerpt', 'Resumen', 320, true, true, 'Una introducción breve que aparecerá en las tarjetas de noticias.'],
  ] as [$fieldName, $label, $limit, $required, $multiline, $help]): ?>
    <?= $view->render('partials/blog-editor-field', compact('fieldName', 'label', 'limit', 'required', 'multiline', 'help', 'editorValues', 'editorErrors')) ?>
  <?php endforeach ?>
  <div class="mb-4"><label for="editorCategory">Categoría *</label><select class="form-select" id="editorCategory" name="category" required><?php foreach (App\Models\Blog::CATEGORIES as $key => $label): ?><option value="<?= e($key) ?>" <?= $editorValues['category'] === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach ?></select><?php if (isset($editorErrors['category'])): ?><p class="editor-error"><?= e($editorErrors['category']) ?></p><?php endif ?></div>
  <?= $view->render('partials/blog-editor-field', ['fieldName' => 'content', 'label' => 'Contenido', 'limit' => 50000, 'required' => true, 'multiline' => true, 'help' => 'Separa los párrafos con una línea en blanco. Para un subtítulo, empieza el párrafo con ## seguido de un espacio.', 'editorValues' => $editorValues, 'editorErrors' => $editorErrors]) ?>
  <fieldset class="mb-4"><legend class="h5">Foto de portada</legend>
    <?php if ($editorValues['image_id']): ?><img class="editor-photo" src="/admin/noticias/imagen/<?= e($editorValues['image_id']) ?>" alt="<?= e($editorValues['image_alt']) ?>"><div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="editorRemoveImage" name="remove_image" value="1"><label class="form-check-label" for="editorRemoveImage">Quitar la foto actual</label></div><?php endif ?>
    <label for="editorPhoto">Seleccionar foto</label><input class="form-control" id="editorPhoto" name="photo" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="editorPhotoHelp"><p class="form-text" id="editorPhotoHelp">JPG, PNG o WebP. Máximo 4 MB. Usa una foto propia o con permiso para publicarla.</p>
    <?php if (isset($editorErrors['photo'])): ?><p class="editor-error"><?= e($editorErrors['photo']) ?></p><?php endif ?>
  </fieldset>
  <?php foreach ([
      ['image_alt', 'Descripción de la foto', 200, false, false, 'Obligatoria cuando incluyes una foto. Describe lo que se ve en ella.'],
      ['source_name', 'Nombre de la fuente', 120, false, false, ''],
      ['source_url', 'Enlace a la fuente', 1000, false, false, 'Si la noticia se basa en otra publicación, enlaza aquí la fuente original.'],
  ] as [$fieldName, $label, $limit, $required, $multiline, $help]): ?>
    <?= $view->render('partials/blog-editor-field', compact('fieldName', 'label', 'limit', 'required', 'multiline', 'help', 'editorValues', 'editorErrors')) ?>
  <?php endforeach ?>
  <?php if (isset($editorErrors['status'])): ?><p class="editor-error"><?= e($editorErrors['status']) ?></p><?php endif ?>
  <?php if ($editorValues['status'] === 'published'): ?><p class="form-text">Esta noticia está publicada. Guardar como borrador la retirará del blog público.</p><?php endif ?>
  <div class="editor-actions"><button class="btn btn-primary-custom" type="submit" name="status" value="published"><?= $editorValues['status'] === 'published' ? 'Actualizar publicación' : 'Publicar noticia' ?></button><button class="btn btn-outline-custom" type="submit" name="status" value="draft">Guardar borrador</button><a href="/admin/noticias">Volver al panel</a></div>
</form>
