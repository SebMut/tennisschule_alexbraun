<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $all=$d['locations']??[]; $loc=$all['feldkirchen']??[];
page_head($loc['name']??'Standort'); site_header('standorte');
?><main><?php site_hero(); ?><section class="club-detail"><div class="container">
<div class="club-content"><h2><?=h($loc['name']??'')?></h2><p><?=h($loc['description']??'')?><?php if(!empty($loc['description_2'])): ?> <?=h($loc['description_2'])?><?php endif; ?></p>
<div class="button-row"><a class="kubio-btn" data-track="club_feldkirchen_website" href="<?=h($loc['website']??'#')?>" target="_blank" rel="noopener"><?=h($loc['button_website']??'Website')?></a>
<a class="kubio-btn" data-track="club_feldkirchen_membership" href="<?=h($loc['membership']??'#')?>" target="_blank" rel="noopener noreferrer"><?=h($loc['button_membership']??'Mitgliedsantrag')?></a>
<a class="kubio-btn club-training-cta" data-track="club_feldkirchen_training" href="<?=h($all['cta_url']??'/kontakt/')?>"><?=h($all['cta_label']??'Training anfragen')?></a></div></div>
<div class="map-shell"><div class="map-consent"><p><?=h($all['map_consent_text']??'')?></p><button type="button" class="kubio-btn" data-load-map><?=h($all['map_consent_button']??'Karte laden')?></button></div>
<iframe class="map-frame" loading="lazy" referrerpolicy="no-referrer-when-downgrade" data-map-src="https://maps.google.com/maps?iwloc=near&amp;output=embed&amp;q=<?=rawurlencode($loc['map_query']??'')?>&amp;z=11" title="<?=h($loc['name']??'')?> Karte" hidden></iframe></div>
</div></section></main><?php site_footer(); ?></body></html>
