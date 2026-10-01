<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $loc=$d['locations']??[];
page_head($loc['page_title']??'Standorte'); site_header('standorte');
?><main><?php site_hero(); ?><section class="locations-page"><div class="container">
<h2><?=h($loc['page_title']??'Unsere Standorte')?></h2><div class="location-cards">
<?php foreach(['feldkirchen'=>'/tsv-feldkirchen/','heimstetten'=>'/sv-heimstetten/'] as $key=>$url): $l=$loc[$key]??[]; ?>
<article class="location-card"><img src="<?=h($l['logo']??'')?>" alt="<?=h($l['name']??'')?>"><a class="kubio-btn" data-track="location_card_<?=h($key)?>" href="<?=h($url)?>"><?=h($l['name']??'')?></a></article>
<?php endforeach; ?></div></div></section></main><?php site_footer(); ?></body></html>
