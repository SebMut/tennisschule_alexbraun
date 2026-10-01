<?php
require dirname(__DIR__) . '/_inc/site.php';
$d = site_data();
page_head('Angebote');
site_header('angebote');
?>
<main>
<?php site_hero(); ?>
<section class="section"><div class="container">
<h2>Unser Angebot</h2>
<div class="section-heading"><p><?= h($d['offers']['intro'] ?? '') ?></p></div>
<div class="offer-grid">
<?php foreach (($d['offers']['items'] ?? []) as $offer): ?>
<article class="offer-panel">
  <h2><?= h($offer['title'] ?? '') ?></h2>
  <button class="btn btn-blue" data-modal="<?= h($offer['id'] ?? '') ?>"><?= h($offer['button'] ?? 'Anmeldung') ?></button>
  <ul><?php foreach (($offer['details'] ?? []) as $detail): ?><li><?= h($detail) ?></li><?php endforeach; ?></ul>
</article>
<?php endforeach; ?>
</div>
<div class="gallery-grid">
<?php foreach (['angebote_1.jpg','angebote_4.jpg','angebote_2.jpg','angebote_5.jpg','angebote_3.jpg','angebote_6.jpg'] as $img): ?>
<img src="/assets/media/<?= h($img) ?>" alt="Tennistraining">
<?php endforeach; ?>
</div>
</div></section>

<?php foreach (($d['offers']['items'] ?? []) as $offer): ?>
<div class="modal" id="<?= h($offer['id'] ?? '') ?>"><div class="modal-card">
<button class="modal-close" aria-label="Schließen">×</button>
<h2><?= h($offer['title'] ?? '') ?></h2>
<p>Für die Anmeldung und weitere Informationen kontaktiere uns bitte direkt.</p>
<a class="btn btn-blue" href="/kontakt/">Zum Kontakt</a>
</div></div>
<?php endforeach; ?>
</main>
<?php site_footer(); ?>
</body></html>
