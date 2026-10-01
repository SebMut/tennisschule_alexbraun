<?php
require dirname(__DIR__) . '/_inc/site.php';
$d = site_data();
page_head('Trainerteam');
site_header('trainerteam');
?>
<main>
<?php site_hero(); ?>
<section class="trainer-section">
  <div class="container">
    <div class="trainer-intro">
      <h2>Trainerteam</h2>
      <p><?= h($d['trainers']['intro'] ?? '') ?></p>
    </div>
    <div class="trainer-grid">
      <?php foreach (($d['trainers']['items'] ?? []) as $trainer): ?>
      <article class="trainer-card">
        <img src="<?= h($trainer['image'] ?? '/assets/media/logo.png') ?>" alt="<?= h(($trainer['name'] ?? '').' - '.($trainer['role'] ?? 'Trainer')) ?>" onerror="this.onerror=null;this.src='/assets/media/logo.png'">
        <div class="trainer-card-body">
          <h3><?= h($trainer['name'] ?? '') ?></h3>
          <p><?= h($trainer['role'] ?? '') ?></p>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
</main>
<?php site_footer(); ?>
</body></html>
