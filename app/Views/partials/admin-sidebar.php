<aside class="crm-sidebar">
  <a href="/admin" class="crm-logo"><img src="<?= e(asset('assets/img/clicomputer-symbol.png')) ?>" alt="" width="36" height="36"><span>Clicomputer<small>ESPACIO DE ADMINISTRACIÓN</small></span></a>
  <p class="crm-nav-label">TU ESPACIO</p>
  <nav aria-label="Administración" class="crm-nav">
    <?php foreach ([['inicio', '/admin', 'grid-1x2', 'Vista general'], ['noticias', '/admin/noticias', 'journal-text', 'Blog y noticias'], ['usuarios', '/admin/usuarios', 'people', 'Usuarios'], ['cuenta', '/admin/mi-cuenta', 'person-circle', 'Mi cuenta']] as [$key, $url, $icon, $label]): ?>
      <?php if ($key === 'usuarios' && $adminUser['role'] !== 'admin') continue ?>
      <a href="<?= e($url) ?>" <?= $adminSection === $key ? 'aria-current="page"' : '' ?>><i class="bi bi-<?= e($icon) ?>" aria-hidden="true"></i><?= e($label) ?></a>
    <?php endforeach ?>
  </nav>
  <div class="crm-sidebar-bottom"><a href="/" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Ver sitio web</a><div class="crm-session"><span class="crm-avatar"><i class="bi bi-person" aria-hidden="true"></i></span><div><strong><?= e($adminUser['display_name'] ?: $adminUser['username']) ?></strong><small><?= e(App\Models\AdminUser::ROLES[$adminUser['role']]) ?></small></div></div>
    <form action="/admin/salir" method="post"><input type="hidden" name="csrf" value="<?= e($editorToken) ?>"><button type="submit" class="crm-signout"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Cerrar sesión</button></form>
  </div>
</aside>
