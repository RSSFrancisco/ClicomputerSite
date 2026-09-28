<!DOCTYPE html>
<html lang="es-MX" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($adminTitle) ?> | Clicomputer CRM</title>
  <link rel="icon" type="image/png" href="<?= e(asset('assets/img/clicomputer-symbol.png')) ?>">
  <?php foreach (['fonts', 'vendor/bootstrap.min', 'vendor/bootstrap-icons.min', 'variables', 'news', 'admin'] as $style): ?>
    <link rel="stylesheet" href="<?= e(asset('css/' . $style . '.css')) ?>">
  <?php endforeach ?>
</head>
<body class="crm-body<?= $adminUser ? '' : ' crm-guest' ?>">
  <a class="crm-skip" href="#contenido">Saltar al contenido</a>
  <?php if ($adminUser): ?>
    <?= $view->render('partials/admin-sidebar', compact('adminUser', 'adminSection', 'editorToken')) ?>
  <?php endif ?>
  <main id="contenido" class="crm-main">
    <?php if ($adminUser): ?>
      <?= $view->render('partials/admin-header', compact('adminTitle', 'adminUser')) ?>
    <?php else: ?>
      <a class="crm-guest-brand" href="/"><img src="<?= e(asset('assets/img/clicomputer-symbol.png')) ?>" alt="" width="38" height="38"> Clicomputer <span>CRM</span></a>
    <?php endif ?>
    <div class="crm-content">
      <?php if ($editorMessage): ?><div class="alert alert-info" role="status"><?= e($editorMessage) ?></div><?php endif ?>
      <?= $content ?>
    </div>
  </main>
</body>
</html>
