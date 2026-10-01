<?php
declare(strict_types=1);
header('X-Robots-Tag: noindex, nofollow, noarchive', true);
require dirname(__DIR__,2) . '/_inc/site.php';
require dirname(__DIR__,2) . '/api/newsletter-lib.php';

$d=site_data(); $cfg=$d['newsletter']??[];
$email=newsletter_normalize_email((string)($_REQUEST['email']??''));
$token=(string)($_REQUEST['token']??'');
$done=false; $valid=false; $message='';

if(filter_var($email,FILTER_VALIDATE_EMAIL) && preg_match('/^[a-f0-9]{64}$/',$token)){
  $items=newsletter_load(); $idx=newsletter_find_index($items,$email);
  if($idx>=0 && !empty($items[$idx]['unsubscribe_hash']) && hash_equals((string)$items[$idx]['unsubscribe_hash'],newsletter_hash($token))){
    $valid=true;
    if($_SERVER['REQUEST_METHOD']==='POST'){
      $items[$idx]['status']='unsubscribed';
      $items[$idx]['unsubscribed_at']=date(DATE_ATOM);
      $items[$idx]['updated_at']=date(DATE_ATOM);
      newsletter_save($items);
      $done=true;
      $message=$cfg['unsubscribed_message']??'Du wurdest vom Newsletter abgemeldet.';
    }
  }
}
page_head($cfg['unsubscribe_page_title']??'Newsletter abmelden'); site_header();
?><main><?php site_hero(); ?>
<section class="newsletter-action-page"><div class="container narrow center"><div class="newsletter-action-card">
<?php if($done): ?>
  <h2><?=h($cfg['unsubscribe_page_title']??'Newsletter abmelden')?></h2><p><?=h($message)?></p><a class="kubio-btn" href="/">Zur Startseite</a>
<?php elseif($valid): ?>
  <h2><?=h($cfg['unsubscribe_page_title']??'Newsletter abmelden')?></h2>
  <p><?=h($cfg['unsubscribe_confirm_text']??'Möchtest du dich wirklich abmelden?')?></p>
  <form method="post"><input type="hidden" name="email" value="<?=h($email)?>"><input type="hidden" name="token" value="<?=h($token)?>"><button class="kubio-btn" type="submit"><?=h($cfg['unsubscribe_button']??'Newsletter abbestellen')?></button></form>
<?php else: ?>
  <h2>Abmeldung nicht möglich</h2><p>Der Abmeldelink ist ungültig oder nicht mehr aktuell.</p><a class="kubio-btn" href="/">Zur Startseite</a>
<?php endif; ?>
</div></div></section>
</main><?php site_footer(); ?></body></html>
