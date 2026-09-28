<?php $post = $page['post']; ?>
<article class="section-padding-sm news-article"><div class="container">
  <header class="news-header">
    <p class="brand-eyebrow"><?= e(App\Models\Blog::CATEGORIES[$post['category']]) ?></p>
    <h1><?= e($post['title']) ?></h1><p><?= e($post['excerpt']) ?></p>
    <p class="news-byline">Por <?= e($site['name']) ?> · <time datetime="<?= e(str_replace(' ', 'T', $post['published_at']) . 'Z') ?>"><?= e(App\Models\Blog::date($post['published_at'])) ?></time></p>
  </header>
  <?php if ($post['image_id']): ?><figure class="news-cover"><img src="/noticias/imagen/<?= e($post['image_id']) ?>" alt="<?= e($post['image_alt']) ?>" fetchpriority="high"></figure><?php endif ?>
  <div class="news-body"><?= $view->render('partials/news-body', compact('post')) ?></div>
  <div class="news-article-footer"><a href="/noticias.html"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver a noticias</a><a href="<?= e('https://wa.me/?text=' . rawurlencode($post['title'] . ' ' . $canonical)) ?>" target="_blank" rel="noopener noreferrer">Compartir por WhatsApp <i class="bi bi-whatsapp" aria-hidden="true"></i></a></div>
</div></article>
