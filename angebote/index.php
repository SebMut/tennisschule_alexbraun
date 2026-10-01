<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $o=$d['offers']??[];
page_head($o['page_title']??'Angebote'); site_header('angebote');
?>
<main><?php site_hero(); ?>
<section class="offers-page-section"><div class="container">
  <h2><?=h($o['page_title']??'Unser Angebot')?></h2>
  <div class="section-heading"><p><?=h($o['intro']??'')?></p></div>

  <div class="offer-grid offer-grid-detailed">
  <?php foreach(($o['items']??[]) as $offer):
    $id=$offer['id']??'';
    $anchor=$id==='sommer'?'sommertraining':($id==='winter'?'wintertraining':'tenniscamps');
    $featured=!empty($offer['featured']);
    $statusKind=$offer['status_kind']??'';
  ?>
    <article id="<?=h($anchor)?>" class="offer-panel offer-detail-card<?= $featured?' is-featured':'' ?>">
      <div class="offer-detail-media">
        <img loading="lazy" decoding="async" src="<?=h($offer['image']??'')?>" alt="<?=h($offer['title']??'')?>">
        <div class="offer-badges">
          <?php if($featured): ?><span class="offer-badge offer-badge-featured"><?=h($o['featured_label']??'Aktuell')?></span><?php endif; ?>
          <?php if(!empty($offer['status'])): ?><span class="offer-badge offer-badge-status <?=h('status-'.$statusKind)?>"><?=h($offer['status'])?></span><?php endif; ?>
        </div>
      </div>

      <div class="offer-detail-body">
        <h3><?=h($offer['title']??'')?></h3>
        <p class="offer-detail-copy"><?=h($offer['home_text']??'')?></p>

        <?php if(!empty($offer['target_group'])): ?>
          <p class="offer-detail-meta"><strong>Für wen:</strong> <?=h($offer['target_group'])?></p>
        <?php endif; ?>
        <?php if(!empty($offer['focus'])): ?>
          <p class="offer-detail-meta"><strong>Schwerpunkte:</strong> <?=h($offer['focus'])?></p>
        <?php endif; ?>

        <dl class="offer-facts">
          <?php if(!empty($offer['training_scope'])): ?><div><dt>Umfang</dt><dd><?=h($offer['training_scope'])?></dd></div><?php endif; ?>
          <?php if(!empty($offer['period'])): ?><div><dt>Zeitraum</dt><dd><?=h($offer['period'])?></dd></div><?php endif; ?>
          <?php if(!empty($offer['location'])): ?><div><dt>Standort</dt><dd><?=h($offer['location'])?></dd></div><?php endif; ?>
          <?php if(!empty($offer['price'])): ?><div><dt>Preis</dt><dd><?=h($offer['price'])?></dd></div><?php endif; ?>
        </dl>

        <div class="offer-actions">
        <?php if($id==='camps'): ?>
          <div class="trainings-btn-wrapper"><button class="trainings-btn" data-track="training_camp_feldkirchen" type="button" data-hover="<?=h($o['hover_text']??'Jetzt anmelden!')?>" data-training-error="<?=h($offer['unavailable_message']??'')?>"><?=hbr($offer['button_feldkirchen']??$offer['button']??'')?></button></div>
          <div class="trainings-btn-wrapper"><button class="trainings-btn" data-track="training_camp_heimstetten" type="button" data-hover="<?=h($o['hover_text']??'Jetzt anmelden!')?>" data-training-error="<?=h($offer['unavailable_message']??'')?>"><?=hbr($offer['button_heimstetten']??$offer['button']??'')?></button></div>
        <?php else: ?>
          <div class="trainings-btn-wrapper"><button class="trainings-btn" data-track="training_<?=h($id)?>" type="button" data-hover="<?=h($o['hover_text']??'Jetzt anmelden!')?>" <?php if(!empty($offer['form_url'])): ?>data-training-url="<?=h($offer['form_url'])?>"<?php endif; ?> data-training-error="<?=h($offer['unavailable_message']??'')?>"><?=h($offer['button']??'')?></button></div>
        <?php endif; ?>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
  </div>
</div></section>

<?php $faq=$d['faq']??[]; if(!empty($faq['enabled']) && !empty($faq['items'])): ?>
<section class="faq-section"><div class="container narrow">
  <div class="section-heading"><h2><?=h($faq['title']??'Häufige Fragen')?></h2><p><?=h($faq['intro']??'')?></p></div>
  <div class="faq-list">
    <?php foreach($faq['items'] as $i=>$item): ?>
    <details class="faq-item">
      <summary><?=h($item['question']??'')?></summary>
      <div class="faq-answer"><p><?=h($item['answer']??'')?></p></div>
    </details>
    <?php endforeach; ?>
  </div>
</div></section>
<?php
render_json_ld([
  '@context'=>'https://schema.org','@type'=>'FAQPage',
  'mainEntity'=>array_map(fn($item)=>[
    '@type'=>'Question','name'=>$item['question']??'',
    'acceptedAnswer'=>['@type'=>'Answer','text'=>$item['answer']??'']
  ],$faq['items'])
]);
endif; ?>

<section class="gallery-section"><div class="container gallery-grid">
<?php $gallery=$o['gallery']??[]; foreach([[0,3],[1,4],[2,5]] as $indexes): ?><div class="gallery-column"><?php foreach($indexes as $idx): if(empty($gallery[$idx])) continue; ?><div class="gallery-item"><img loading="lazy" decoding="async" src="<?=h($gallery[$idx])?>" alt="Tennistraining"></div><?php endforeach; ?></div><?php endforeach; ?>
</div></section>

<div class="trainings-modal" id="trainingModal" aria-hidden="true"><div class="trainings-modal-content" role="dialog" aria-modal="true"><button class="trainings-close" type="button" aria-label="Schließen">X</button><div class="trainings-form-content" id="trainingModalContent"></div></div></div>
</main><?php site_footer(); ?></body></html>
