<?php
require dirname(__DIR__) . '/_inc/site.php';
$d = site_data();
page_head('Trainerteam');
site_header('trainerteam');
?>
<main>
<?php site_hero(); ?>
<section class="section"><div class="container">
<h2>Trainerteam</h2>
<div class="section-heading"><p><?= h($d['trainers']['intro'] ?? '') ?></p></div>
<div class="trainer-grid">
<?php foreach (($d['trainers']['items'] ?? []) as $trainer): ?>
<article class="trainer-card">
  <img src="<?= h($trainer['image'] ?? '/assets/media/logo.png') ?>" alt="<?= h($trainer['name'] ?? '') ?>" onerror="this.onerror=null;this.src='/assets/media/logo.png'">
  <h3><?= h($trainer['name'] ?? '') ?></h3>
  <p><?= h($trainer['role'] ?? '') ?></p>
</article>
<?php endforeach; ?>
</div>
</div></section>
</main>
<?php site_footer(); ?>
</body></html>
