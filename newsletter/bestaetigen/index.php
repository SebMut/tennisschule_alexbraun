<?php
declare(strict_types=1);
header('X-Robots-Tag: noindex, nofollow, noarchive', true);
require dirname(__DIR__,2) . '/_inc/site.php';
require dirname(__DIR__,2) . '/api/newsletter-lib.php';
require dirname(__DIR__,2) . '/api/smtp.php';

$d=site_data(); $cfg=$d['newsletter']??[];
$email=newsletter_normalize_email((string)($_GET['email']??''));
$token=(string)($_GET['token']??'');
$status='error'; $message=$cfg['error_message']??'Die Anmeldung konnte nicht bestätigt werden.';

if(filter_var($email,FILTER_VALIDATE_EMAIL) && preg_match('/^[a-f0-9]{64}$/',$token)){
  $items=newsletter_load(); $idx=newsletter_find_index($items,$email);
  if($idx>=0){
    $item=$items[$idx];
    if(($item['status']??'')==='active'){
      $status='success'; $message=$cfg['confirmed_message']??'Deine Anmeldung ist bereits bestätigt.';
    }elseif(!empty($item['confirm_hash']) && hash_equals((string)$item['confirm_hash'],newsletter_hash($token))){
      $unsubscribeToken=newsletter_token();
      $items[$idx]['status']='active';
      $items[$idx]['confirmed_at']=date(DATE_ATOM);
      $items[$idx]['updated_at']=date(DATE_ATOM);
      $items[$idx]['confirm_hash']=null;
      $items[$idx]['unsubscribe_hash']=newsletter_hash($unsubscribeToken);
      newsletter_save($items);

      $base=newsletter_base_url();
      $unsubUrl=$base.'/newsletter/abmelden/?email='.rawurlencode($email).'&token='.rawurlencode($unsubscribeToken);
      try{
        smtp_send([
          'to'=>$email,
          'subject'=>$cfg['welcome_subject']??'Newsletter-Anmeldung bestätigt',
          'body'=>($cfg['welcome_text']??'Deine Newsletter-Anmeldung ist bestätigt.')."\n\nNewsletter abbestellen:\n".$unsubUrl
        ]);
      }catch(Throwable $e){}
      $status='success'; $message=$cfg['confirmed_message']??'Deine Newsletter-Anmeldung ist bestätigt.';
    }
  }
}
page_head($cfg['page_title']??'Newsletter'); site_header();
?><main><?php site_hero(); ?>
<section class="newsletter-action-page"><div class="container narrow center">
  <div class="newsletter-action-card <?=h($status)?>">
    <h2><?=h($status==='success'?'Newsletter bestätigt':'Bestätigung nicht möglich')?></h2>
    <p><?=h($message)?></p>
    <a class="kubio-btn" href="/">Zur Startseite</a>
  </div>
</div></section>
</main><?php site_footer(); ?></body></html>
