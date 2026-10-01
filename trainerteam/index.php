<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $t=$d['trainers']??[];
page_head($t['page_title']??'Trainerteam'); site_header('trainerteam');
?><main><?php site_hero(); ?><section class="trainer-section"><div class="container">
<div class="trainer-intro"><h2><?=h($t['page_title']??'Trainerteam')?></h2><p><?=h($t['intro']??'')?></p></div>
<div class="trainer-grid"><?php foreach(($t['items']??[]) as $tr): ?><article class="trainer-card"><img src="<?=h($tr['image']??'')?>" alt="<?=h(($tr['name']??'').' - '.($tr['role']??''))?>"><div class="trainer-card-body"><h3><?=h($tr['name']??'')?></h3><p><?=h($tr['role']??'')?></p></div></article><?php endforeach; ?></div>
</div></section></main><?php site_footer(); ?></body></html>
