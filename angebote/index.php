<?php
require dirname(__DIR__) . '/_inc/site.php';
$d = site_data();
page_head('Angebote');
site_header('angebote');
?>
<main>
<?php site_hero(); ?>
<section class="offers-page-section">
  <div class="container">
    <h2>Unser Angebot</h2>
    <div class="section-heading"><p><?= h($d['offers']['intro'] ?? '') ?></p></div>
    <div class="offer-grid">
      <?php foreach (($d['offers']['items'] ?? []) as $offer): $id=$offer['id'] ?? ''; ?>
      <article class="offer-panel">
        <h3><?= h($offer['title'] ?? '') ?></h3>
        <?php if ($id === 'sommer'): ?>
          <div class="trainings-btn-wrapper"><button class="trainings-btn" type="button" data-hover="Jetzt anmelden!" data-training-error="Ab ca. 10.03.2027 buchbar.">Anmeldung Sommertraining</button></div>
        <?php elseif ($id === 'winter'): ?>
          <div class="trainings-btn-wrapper"><button class="trainings-btn" type="button" data-hover="Jetzt anmelden!" data-training-url="https://docs.google.com/forms/d/e/1FAIpQLSc_Fct_pc8R9KGUg8YF5Vp74Mz7lX1x6GMRE15QQRTOLrAGzA/viewform?pli=1/viewform?embedded=true" data-training-error="Ab ca. 28.08.2026 buchbar.">Anmeldung Wintertraining</button></div>
        <?php else: ?>
          <div class="trainings-btn-wrapper"><button class="trainings-btn" type="button" data-hover="Jetzt anmelden!" data-training-error="Ab ca. 10.03.2027 buchbar.">Anmeldung Tenniscamps<br>TSV Feldkirchen</button></div>
          <div class="trainings-btn-wrapper"><button class="trainings-btn" type="button" data-hover="Jetzt anmelden!" data-training-error="Ab ca. 10.03.2027 buchbar.">Anmeldung Tenniscamps<br>SV Heimstetten</button></div>
        <?php endif; ?>
        <ul><?php foreach (($offer['details'] ?? []) as $detail): ?><li><?= h($detail) ?></li><?php endforeach; ?></ul>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="gallery-section">
  <div class="container gallery-grid">
    <?php foreach ([['angebote_1.jpg','angebote_4.jpg'],['angebote_2.jpg','angebote_5.jpg'],['angebote_3.jpg','angebote_6.jpg']] as $col): ?>
    <div class="gallery-column">
      <?php foreach ($col as $img): ?><div class="gallery-item"><img src="/assets/media/<?= h($img) ?>" alt="Tennistraining"></div><?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<div class="trainings-modal" id="trainingModal" aria-hidden="true">
  <div class="trainings-modal-content" role="dialog" aria-modal="true" aria-label="Trainingsanmeldung">
    <button class="trainings-close" type="button" aria-label="Schließen">X</button>
    <div class="trainings-form-content" id="trainingModalContent"></div>
  </div>
</div>
</main>
<?php site_footer(); ?>
</body></html>
