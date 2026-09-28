<?php foreach (preg_split('/\R\s*\R/u', $post['content']) as $paragraph): ?>
  <?php if (str_starts_with($paragraph, '## ')): ?>
    <h2><?= e(substr($paragraph, 3)) ?></h2>
  <?php else: ?>
    <p><?= nl2br(e($paragraph)) ?></p>
  <?php endif ?>
<?php endforeach ?>
<?php if ($post['source_url']): ?><aside class="news-source"><strong>Fuente:</strong> <a href="<?= e($post['source_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($post['source_name'] ?: 'Consultar la publicación original') ?></a></aside><?php endif ?>
