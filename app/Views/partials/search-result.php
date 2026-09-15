<a class="site-search-result" href="<?= e($result['url']) ?>">
  <span class="site-search-result-icon" aria-hidden="true"><i class="bi <?= e($result['icon']) ?>"></i></span>
  <div class="site-search-result-copy">
    <span class="site-search-category"><?= e($result['category']) ?></span>
    <h2 class="site-search-title"><?= e($result['title']) ?></h2>
    <span class="site-search-description"><?= e($result['description']) ?></span>
  </div>
  <i class="bi bi-arrow-up-right site-search-arrow" aria-hidden="true"></i>
</a>
