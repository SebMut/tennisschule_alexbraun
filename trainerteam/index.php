<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $t=$d['trainers']??[];
page_head($t['page_title']??'Trainerteam'); site_header('trainerteam');
?><main><?php site_hero(); ?><section class="trainer-section"><div class="container">
<div class="trainer-intro"><h2><?=h($t['page_title']??'Trainerteam')?></h2><p><?=h($t['intro']??'')?></p></div>
<div class="trainer-grid"><?php foreach(($t['items']??[]) as $i=>$tr): $hasDetails=!empty($tr['qualifications'])||!empty($tr['experience'])||!empty($tr['focus'])||!empty($tr['bio']); ?>
<article class="trainer-card">
  <img loading="lazy" src="<?=h($tr['image']??'')?>" alt="<?=h(($tr['name']??'').' - '.($tr['role']??''))?>">
  <div class="trainer-card-body"><h3><?=h($tr['name']??'')?></h3><p><?=h($tr['role']??'')?></p>
  <?php if($hasDetails && !empty($t['details_enabled'])): ?><button class="trainer-more" type="button" data-trainer-toggle="<?=h((string)$i)?>" aria-expanded="false">Mehr erfahren</button><?php endif; ?></div>
  <?php if($hasDetails && !empty($t['details_enabled'])): ?><div class="trainer-details" id="trainer-details-<?=h((string)$i)?>" hidden>
    <?php if(!empty($tr['qualifications'])): ?><p><strong>Qualifikationen</strong><span><?=h($tr['qualifications'])?></span></p><?php endif; ?>
    <?php if(!empty($tr['experience'])): ?><p><strong>Erfahrung</strong><span><?=h($tr['experience'])?></span></p><?php endif; ?>
    <?php if(!empty($tr['focus'])): ?><p><strong>Schwerpunkte</strong><span><?=h($tr['focus'])?></span></p><?php endif; ?>
    <?php if(!empty($tr['bio'])): ?><p><?=h($tr['bio'])?></p><?php endif; ?>
  </div><?php endif; ?>
</article>
<?php endforeach; ?></div>
<div class="page-cta-row"><a class="kubio-btn" data-track="trainers_cta" href="<?=h($t['cta_url']??'/kontakt/')?>"><?=h($t['cta_label']??'Training anfragen')?></a></div>
</div></section></main><?php site_footer(); ?></body></html>