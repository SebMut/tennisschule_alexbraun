<?php
require __DIR__ . '/_inc/site.php';
$d=site_data(); $home=$d['home']??[]; $loc=$d['locations']??[];
page_head('Home','Seit Mai 2023 leitet die Tennisschule Alex Braun das Training beim TSV Feldkirchen. Ein motiviertes Team fördert Talente, Leistungssportler & den Breitensport.',true);
site_header('home');
?>
<main>
<?php site_hero(true); ?><div id="content-start"></div>

<section class="section about-section"><div class="container about-grid">
  <div class="about-copy"><h2><?=h($home['about_title']??'Über uns')?></h2>
  <?php foreach(($home['about_paragraphs']??[]) as $i=>$p): ?><p><?php
    $safe=h($p);
    if($i===0) $safe=str_replace(
      [h($loc['feldkirchen']['name']??'TSV Feldkirchen'),h($loc['heimstetten']['name']??'SV Heimstetten')],
      ['<a href="/tsv-feldkirchen/">'.h($loc['feldkirchen']['name']??'TSV Feldkirchen').'</a>','<a href="/sv-heimstetten/">'.h($loc['heimstetten']['name']??'SV Heimstetten').'</a>'],$safe);
    echo $safe;
  ?></p><?php endforeach; ?></div>
  <div class="kubio-shadow-image"><img src="<?=h($home['about_image']??'')?>" alt="<?=h($home['about_title']??'Über uns')?>"></div>
</div></section>

<section class="offers-home"><div class="container">
  <div class="section-heading"><h2><?=h($home['offers_title']??'Unser Angebot')?></h2><p><?=h($home['offers_intro']??'')?></p></div>
  <div class="cards three"><?php foreach(($d['offers']['items']??[]) as $offer): ?>
    <article class="offer-card"><img src="<?=h($offer['image']??'')?>" alt="<?=h($offer['title']??'')?>">
      <div class="offer-card-body"><h3><?=h($offer['title']??'')?></h3><p><?=h($offer['home_text']??'')?></p>
      <a class="text-link" data-track="home_offer_<?=h($offer['id']??'offer')?>" href="<?=h($home['offer_link_url']??'/angebote/')?>"><?=h($home['offer_link_text']??'Zur Anmeldung')?></a></div>
    </article>
  <?php endforeach; ?></div>
</div></section>

<section class="team-teaser"><div class="container">
  <div class="section-heading"><h2><?=h($home['team_title']??'Unser Trainerteam')?></h2><p><?=h($home['team_intro']??'')?></p></div>
  <div class="team-visual" style="<?=h("--team-image:url('".str_replace(["'",'"',')','\\'],'',(string)($home['team_image']??''))."')")?>">
    <div class="team-visual-action"><a class="kubio-btn" data-track="home_team" href="<?=h($home['team_button_url']??'/trainerteam/')?>"><?=h($home['team_button_text']??'Zur Trainerübersicht')?></a></div>
  </div>
</div></section>

<section class="home-locations-heading"><div class="container"><h2><?=h($home['locations_title']??'Unsere Standorte')?></h2></div></section>
<section class="home-locations"><div class="container home-locations-grid">
  <?php foreach(['feldkirchen'=>'/tsv-feldkirchen/','heimstetten'=>'/sv-heimstetten/'] as $key=>$url): $l=$loc[$key]??[]; ?>
  <article class="home-location"><h2><?=h($l['name']??'')?></h2><p><?=h($l['description']??'')?><?php if(!empty($l['description_2'])): ?> <?=h($l['description_2'])?><?php endif; ?></p></article>
  <?php endforeach; ?>
</div></section>
<section class="home-map-wrap"><div class="container">
  <div id="mapbox-container" class="mapbox-home" data-mapbox-token="<?=h(mapbox_public_token())?>" data-route-label="<?=h($home['map_route_label']??'Route berechnen')?>" aria-label="<?=h($home['locations_title']??'Standorte')?>"></div>
  <script type="application/json" id="mapbox-location-data"><?=json_encode([
    ['key'=>'feldkirchen','name'=>$loc['feldkirchen']['name']??'TSV Feldkirchen','lat'=>48.143427223206544,'lng'=>11.719520683624244,'logo'=>$loc['feldkirchen']['marker_image']??'/assets/media/mapbox-tsv.png','link'=>$loc['feldkirchen']['marker_link']??'#'],
    ['key'=>'heimstetten','name'=>$loc['heimstetten']['name']??'SV Heimstetten','lat'=>48.16306908434989,'lng'=>11.74709917555206,'logo'=>$loc['heimstetten']['marker_image']??'/assets/media/mapbox-svh.png','link'=>$loc['heimstetten']['marker_link']??'#']
  ],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)?></script>
</div></section>

<?php if(($home['popup_enabled']??true)): ?>
<div class="wp-popup-overlay" id="winterPopup" aria-hidden="true"
 data-frequency-days="<?=h((string)($home['popup_frequency_days']??7))?>"
 data-delay-seconds="<?=h((string)($home['popup_delay_seconds']??1))?>"
 data-close-delay-seconds="<?=h((string)($home['popup_close_delay_seconds']??2))?>"
 data-popup-key="<?=h(md5((string)($home['popup_image']??'').(string)($home['popup_link']??'')))?>">
  <div class="wp-popup" role="dialog" aria-modal="true" aria-label="<?=h($home['popup_alt']??'Popup')?>">
    <button class="wp-popup-close" type="button" aria-label="Schließen">X</button>
    <a data-track="popup_click" href="<?=h($home['popup_link']??'/angebote/')?>"><img src="<?=h($home['popup_image']??'')?>" alt="<?=h($home['popup_alt']??'')?>"></a>
  </div>
</div>
<?php endif; ?>
</main>
<?php site_footer(); ?></body></html>
