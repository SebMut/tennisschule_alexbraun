<?php
require dirname(__DIR__) . '/_inc/site.php';
$d = site_data();
page_head('Standorte');
site_header('standorte');
?>
<main>
<?php site_hero(); ?>
<section class="locations-page">
  <div class="container">
    <h2>Unsere Standorte</h2>
    <div class="location-cards">
      <?php foreach (['feldkirchen'=>['/tsv-feldkirchen/','TSV Feldkirchen'],'heimstetten'=>['/sv-heimstetten/','SV Heimstetten']] as $key=>$meta): $loc=$d['locations'][$key] ?? []; ?>
      <article class="location-card">
        <img src="<?= h($loc['logo'] ?? '') ?>" alt="<?= h($loc['name'] ?? '') ?>">
        <a class="kubio-btn" href="<?= h($meta[0]) ?>"><?= h($meta[1]) ?></a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
</main>
<?php site_footer(); ?>
</body></html>
