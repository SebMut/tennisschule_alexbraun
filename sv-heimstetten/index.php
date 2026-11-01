<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $all=$d['locations']??[]; $loc=$all['heimstetten']??[];
page_head($loc['name']??'Standort'); site_header('standorte');
?><main><?php site_hero(); ?><section class="club-detail"><div class="container">
<div class="club-content"><p><?=h($loc['description']??'')?><?php if(!empty($loc['description_2'])): ?> <?=h($loc['description_2'])?><?php endif; ?></p>
<div class="button-row"><a class="kubio-btn" data-track="club_heimstetten_website" href="<?=h($loc['website']??'#')?>" target="_blank" rel="noopener"><?=h($loc['button_website']??'Website')?></a>
<a class="kubio-btn" data-track="club_heimstetten_membership" href="<?=h($loc['membership']??'#')?>" target="_blank" rel="noopener noreferrer"><?=h($loc['button_membership']??'Mitgliedsantrag')?></a>
<a class="kubio-btn club-training-cta" data-track="club_heimstetten_training" href="<?=h($all['cta_url']??'/kontakt/')?>"><?=h($all['cta_label']??'Training anfragen')?></a></div></div>

<div class="map-shell mapbox-location-shell" id="standortkarte">
  <div id="mapbox-container" class="mapbox-location"
    data-mapbox-token="<?=h(mapbox_public_token())?>"
    data-mapbox-single="1"
    data-route-label="<?=h($all['map_route_button']??'Route mit Mapbox berechnen')?>"
    data-route-loading="<?=h($all['map_route_loading']??'Standort wird ermittelt …')?>"
    data-route-error="<?=h($all['map_route_error']??'Dein Standort konnte nicht ermittelt werden.')?>"
    aria-label="<?=h(($loc['name']??'Standort').' Karte')?>"></div>
  <script type="application/json" id="mapbox-location-data"><?=json_encode([[
    'key'=>'heimstetten',
    'name'=>$loc['name']??'',
    'fact'=>$loc['fact']??'',
    'detail_url'=>'/sv-heimstetten/',
    'lat'=>(float)($loc['latitude']??0),
    'lng'=>(float)($loc['longitude']??0),
    'logo'=>$loc['marker_image']??''
  ]],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)?></script>
  <div class="mapbox-route-bar">
    <button type="button" class="kubio-btn mapbox-route-button" data-mapbox-route><?=h($all['map_route_button']??'Route mit Mapbox berechnen')?></button>
    <span class="mapbox-route-status" data-mapbox-route-status aria-live="polite"></span>
  </div>
</div>
</div></section></main><?php site_footer(); ?></body></html>