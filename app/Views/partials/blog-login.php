<div class="editor-panel editor-login">
  <p class="brand-eyebrow">Clicomputer · Acceso privado</p><h1 class="h3">Entra a tu CRM</h1><p>Administra tu equipo y las noticias de Clicomputer.</p>
  <form class="editor-form" method="post" action="/admin/entrar">
    <input type="hidden" name="csrf" value="<?= e($editorToken) ?>">
    <div class="mb-3"><label for="editorUsername">Usuario</label><input class="form-control" id="editorUsername" name="username" maxlength="80" autocomplete="username" required autofocus></div>
    <div class="mb-4"><label for="editorPassword">Contraseña</label><input class="form-control" type="password" id="editorPassword" name="password" maxlength="1024" autocomplete="current-password" required></div>
    <button class="btn btn-primary-custom w-100" type="submit">Entrar al panel</button>
  </form>
</div>
