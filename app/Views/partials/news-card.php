<article class="news-card">
  <a class="news-card-visual" href="/noticias/<?= e($post['slug']) ?>.html" tabindex="-1" aria-hidden="true">
    <?php if ($post['image_id']): ?>
      <img src="/noticias/imagen/<?= e($post['image_id']) ?>" alt="" loading="lazy" width="720" height="450">
    <?php else: ?>
      <span class="news-placeholder news-placeholder-<?= e($post['category']) ?>"><i class="bi <?= $post['category'] === 'tecnologia' ? 'bi-cpu' : 'bi-geo-alt' ?>" aria-hidden="true"></i></span>
    <?php endif ?>
  </a>
  <div class="news-card-body">
    <p class="news-meta"><span><?= e(App\Models\Blog::CATEGORIES[$post['category']]) ?></span><time datetime="<?= e(str_replace(' ', 'T', $post['published_at']) . 'Z') ?>"><?= e(App\Models\Blog::date($post['published_at'])) ?></time></p>
    <h2 class="h4"><a href="/noticias/<?= e($post['slug']) ?>.html"><?= e($post['title']) ?></a></h2>
    <p><?= e($post['excerpt']) ?></p>
    <a class="service-card-link" href="/noticias/<?= e($post['slug']) ?>.html">Leer noticia <span class="visually-hidden">: <?= e($post['title']) ?></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
  </div>
</article>
