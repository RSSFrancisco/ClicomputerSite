<div class="mb-4">
  <label for="editor-<?= e($fieldName) ?>"><?= e($label) ?><?= $required ? ' *' : '' ?></label>
  <?php if ($multiline): ?>
    <textarea class="form-control" id="editor-<?= e($fieldName) ?>" name="<?= e($fieldName) ?>" maxlength="<?= $limit ?>" <?= $required ? 'required' : '' ?> <?= isset($editorErrors[$fieldName]) ? 'aria-invalid="true" aria-describedby="error-' . e($fieldName) . '"' : '' ?>><?= e($editorValues[$fieldName] ?? '') ?></textarea>
  <?php else: ?>
    <input class="form-control" id="editor-<?= e($fieldName) ?>" name="<?= e($fieldName) ?>" type="<?= $fieldName === 'source_url' ? 'url' : 'text' ?>" maxlength="<?= $limit ?>" value="<?= e($editorValues[$fieldName] ?? '') ?>" <?= $required ? 'required' : '' ?> <?= isset($editorErrors[$fieldName]) ? 'aria-invalid="true" aria-describedby="error-' . e($fieldName) . '"' : '' ?>>
  <?php endif ?>
  <?php if ($help): ?><p class="form-text"><?= e($help) ?></p><?php endif ?>
  <?php if (isset($editorErrors[$fieldName])): ?><p class="editor-error" id="error-<?= e($fieldName) ?>"><?= e($editorErrors[$fieldName]) ?></p><?php endif ?>
</div>
