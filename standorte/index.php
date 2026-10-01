<?php
require dirname(__DIR__) . '/_inc/site.php';
$d = site_data();
page_head('Standorte');
site_header('standorte');
?>
<main>
<?php site_hero(); ?>
<section class="section"><div class="container">
<h2>Unsere Standorte</h2>
<div class="location-cards">
<?php foreach (['feldkirchen'=>'/tsv-feldkirchen/','heimstetten'=>'/sv-heimstetten/'] as $key=>$url): $loc=$d['locations'][$key] ?? []; ?>
<a class="location-card" href="<?= h($url) ?>">
  <img src="<?= h($loc['logo'] ?? '') ?>" alt="<?= h($loc['name'] ?? '') ?>">
  <h2><?= h($loc['name'] ?? '') ?></h2>
</a>
<?php endforeach; ?>
</div>
</div></section>
</main>
<?php site_footer(); ?>
</body></html>
