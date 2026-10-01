<?php
require dirname(__DIR__) . '/_inc/site.php';
$d = site_data();
$loc = $d['locations']['heimstetten'] ?? [];
page_head('SV Heimstetten');
site_header('standorte');
?>
<main>
<?php site_hero(); ?>
<section class="section"><div class="container">
<div class="content-prose">
<h2><?= h($loc['name'] ?? 'SV Heimstetten') ?></h2>
<p><?= h($loc['description'] ?? '') ?></p>
<?php if (!empty($loc['description_2'])): ?><p><?= h($loc['description_2']) ?></p><?php endif; ?>
<div class="button-row">
<a class="btn btn-blue" href="<?= h($loc['website'] ?? '#') ?>" target="_blank" rel="noopener">WEBSITE SV Heimstetten</a>
<a class="btn btn-primary" href="<?= h($loc['membership'] ?? '#') ?>" target="_blank" rel="noopener">Zum Mitgliedsantrag</a>
</div>
</div>
<div class="map-consent">
      <p>Google Maps wird erst nach deiner Zustimmung geladen. Dabei können Daten an Google übertragen werden.</p>
      <button type="button" class="btn btn-blue" data-load-map>Karte laden</button>
    </div>
    <iframe class="map-frame" loading="lazy" referrerpolicy="no-referrer-when-downgrade" data-map-src="https://maps.google.com/maps?iwloc=near&amp;output=embed&amp;q=<?= rawurlencode($loc['map_query'] ?? 'SV Heimstetten') ?>&amp;z=11" title="SV Heimstetten Karte" hidden></iframe>
</div></section>
</main>
<?php site_footer(); ?>
</body></html>
