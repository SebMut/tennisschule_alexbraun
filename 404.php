<?php
http_response_code(404);
require __DIR__ . '/_inc/site.php';
$d=site_data(); $n=$d['not_found']??[];
page_head($n['title']??'Seite nicht gefunden'); site_header();
?><main><?php site_hero(); ?><section class="section error-page"><div class="container narrow center">
<h2><?=h($n['title']??'Seite nicht gefunden')?></h2><p><?=h($n['text']??'')?></p>
<div class="button-row"><a class="kubio-btn" data-track="404_home" href="<?=h($n['primary_url']??'/')?>"><?=h($n['primary_label']??'Zur Startseite')?></a>
<a class="kubio-btn" data-track="404_offers" href="<?=h($n['secondary_url']??'/angebote/')?>"><?=h($n['secondary_label']??'Zu den Angeboten')?></a></div>
</div></section></main><?php site_footer(); ?></body></html>