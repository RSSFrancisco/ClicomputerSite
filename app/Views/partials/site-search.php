<!-- El formulario GET funciona incluso si JavaScript o las sugerencias no cargan. -->
<form class="nav-search" id="siteSearchForm" action="/buscar" method="get" role="search" aria-label="Búsqueda del sitio">
  <button type="submit" class="nav-search-submit" aria-label="Buscar"><i class="bi bi-search" aria-hidden="true"></i></button>
  <input type="search" class="nav-search-input" placeholder="Buscar..." aria-label="Buscar en todo el sitio"
    id="globalSearch" name="q" minlength="2" maxlength="120" required value="<?= e($searchQuery ?? '') ?>" autocomplete="off">
  <span class="nav-search-shortcut" aria-hidden="true">/</span>
  <button type="button" class="nav-search-clear" aria-label="Limpiar búsqueda" hidden><i class="bi bi-x-lg" aria-hidden="true"></i></button>
  <div class="site-search-panel" id="searchPanel" hidden>
    <p class="site-search-status" id="searchStatus" role="status" aria-live="polite"></p>
    <ul class="site-search-options list-unstyled mb-0" id="searchSuggestions" role="listbox" aria-label="Sugerencias de búsqueda"></ul>
    <a class="site-search-all" id="searchAll" href="/buscar">Ver todos los resultados <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
  </div>
</form>
