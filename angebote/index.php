<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $o=$d['offers']??[];
page_head($o['page_title']??'Angebote'); site_header('angebote');
?>
<main><?php site_hero(); ?>
<section class="offers-page-section"><div class="container">
<h2><?=h($o['page_title']??'Unser Angebot')?></h2><div class="section-heading"><p><?=h($o['intro']??'')?></p></div>
<div class="offer-grid"><?php foreach(($o['items']??[]) as $offer): $id=$offer['id']??''; ?>
<article class="offer-panel"><h3><?=h($offer['title']??'')?></h3>
<?php if($id==='camps'): ?>
<div class="trainings-btn-wrapper"><button class="trainings-btn" data-track="training_camp_feldkirchen" type="button" data-hover="<?=h($o['hover_text']??'Jetzt anmelden!')?>" data-training-error="<?=h($offer['unavailable_message']??'')?>"><?=hbr($offer['button_feldkirchen']??$offer['button']??'')?></button></div>
<div class="trainings-btn-wrapper"><button class="trainings-btn" data-track="training_camp_heimstetten" type="button" data-hover="<?=h($o['hover_text']??'Jetzt anmelden!')?>" data-training-error="<?=h($offer['unavailable_message']??'')?>"><?=hbr($offer['button_heimstetten']??$offer['button']??'')?></button></div>
<?php else: ?>
<div class="trainings-btn-wrapper"><button class="trainings-btn" data-track="training_<?=h($id)?>" type="button" data-hover="<?=h($o['hover_text']??'Jetzt anmelden!')?>" <?php if(!empty($offer['form_url'])): ?>data-training-url="<?=h($offer['form_url'])?>"<?php endif; ?> data-training-error="<?=h($offer['unavailable_message']??'')?>"><?=h($offer['button']??'')?></button></div>
<?php endif; ?>
<ul><?php foreach(($offer['details']??[]) as $detail): ?><li><?=h($detail)?></li><?php endforeach; ?></ul></article>
<?php endforeach; ?></div></div></section>
<section class="gallery-section"><div class="container gallery-grid">
<?php $gallery=$o['gallery']??[]; foreach(array_chunk($gallery,2) as $col): ?><div class="gallery-column"><?php foreach($col as $img): ?><div class="gallery-item"><img src="<?=h($img)?>" alt="Tennistraining"></div><?php endforeach; ?></div><?php endforeach; ?>
</div></section>
<div class="trainings-modal" id="trainingModal" aria-hidden="true"><div class="trainings-modal-content" role="dialog" aria-modal="true"><button class="trainings-close" type="button" aria-label="Schließen">X</button><div class="trainings-form-content" id="trainingModalContent"></div></div></div>
</main><?php site_footer(); ?></body></html>
