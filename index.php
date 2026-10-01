<?php
require __DIR__ . '/_inc/site.php';
$d = site_data();
page_head('Home', 'Tennisschule Alex Braun – Deine Tennisschule im Münchner Osten.');
site_header('home');
?>
<main>
<?php site_hero(true); ?>
<section class="section">
  <div class="container split">
    <div>
      <h2><?= h($d['home']['about_title'] ?? 'Über uns') ?></h2>
      <?php foreach (($d['home']['about_paragraphs'] ?? []) as $i => $p): ?>
        <p>
          <?php if ($i === 0): ?>
            <?= str_replace(
              ['TSV Feldkirchen','SV Heimstetten'],
              ['<a href="/tsv-feldkirchen/">TSV Feldkirchen</a>','<a href="/sv-heimstetten/">SV Heimstetten</a>'],
              h($p)
            ) ?>
          <?php else: ?>
            <?= h($p) ?>
          <?php endif; ?>
        </p>
      <?php endforeach; ?>
    </div>
    <div class="media-frame portrait">
      <img src="<?= h($d['home']['about_image'] ?? '/assets/media/startseite_1.jpg') ?>" alt="Alex Braun">
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="section-heading">
      <h2><?= h($d['home']['offers_title'] ?? 'Unser Angebot') ?></h2>
      <p><?= h($d['home']['offers_intro'] ?? '') ?></p>
    </div>
    <div class="cards three">
      <?php foreach (($d['offers']['items'] ?? []) as $offer): ?>
      <article class="card">
        <img src="<?= h($offer['image'] ?? '') ?>" alt="<?= h($offer['title'] ?? '') ?>">
        <div class="card-body">
          <h3><?= h($offer['title'] ?? '') ?></h3>
          <p><?= h($offer['home_text'] ?? '') ?></p>
          <a class="text-link" href="/angebote/">Zur Anmeldung</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section team-teaser">
  <div class="container narrow center">
    <h2><?= h($d['home']['team_title'] ?? 'Unser Trainerteam') ?></h2>
    <p><?= h($d['home']['team_intro'] ?? '') ?></p>
    <a class="btn btn-primary" href="/trainerteam/">Zur Trainerübersicht</a>
  </div>
</section>

<section class="section section-dark">
  <div class="container">
    <h2>Unsere Standorte</h2>
    <div class="location-grid">
      <?php foreach (['feldkirchen','heimstetten'] as $key): $loc = $d['locations'][$key] ?? []; ?>
      <article>
        <h3><?= h($loc['name'] ?? '') ?></h3>
        <p><?= h($loc['description'] ?? '') ?></p>
        <?php if (!empty($loc['description_2'])): ?><p><?= h($loc['description_2']) ?></p><?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
</main>
<?php site_footer(); ?>
</body></html>
