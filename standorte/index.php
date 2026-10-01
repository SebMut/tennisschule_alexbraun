<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $loc=$d['locations']??[];
page_head($loc['page_title']??'Standorte'); site_header('standorte');
?><main><?php site_hero(); ?><section class="locations-page"><div class="container">
<h2><?=h($loc['page_title']??'Unsere Standorte')?></h2><div class="location-cards">
<?php foreach(['feldkirchen'=>'/tsv-feldkirchen/','heimstetten'=>'/sv-heimstetten/'] as $key=>$url): $l=$loc[$key]??[]; ?>
<article class="location-card">
  <img loading="lazy" src="<?=h($l['logo']??'')?>" alt="<?=h($l['name']??'')?>">
  <h3><?=h($l['name']??'')?></h3>
  <?php if(!empty($l['fact'])): ?><p class="location-fact"><?=h($l['fact'])?></p><?php endif; ?>
  <p><?=h($l['overview_text']??'')?></p>
  <div class="location-actions">
    <a class="kubio-btn" data-track="location_card_<?=h($key)?>" href="<?=h($url)?>"><?=h($loc['location_button']??'Standort ansehen')?></a>
    <a class="location-route" data-track="location_route_<?=h($key)?>" href="<?=h($l['marker_link']??'#')?>" target="_blank" rel="noopener"><?=h($loc['route_button']??'Route berechnen')?></a>
  </div>
</article>
<?php endforeach; ?></div>
<div class="page-cta-row"><a class="kubio-btn" data-track="locations_cta" href="<?=h($loc['cta_url']??'/kontakt/')?>"><?=h($loc['cta_label']??'Training anfragen')?></a></div>
</div></section></main><?php site_footer(); ?></body></html>