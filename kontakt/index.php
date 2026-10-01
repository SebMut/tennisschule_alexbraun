<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $c=$d['contact']??[]; $labels=$c['labels']??[];
page_head($c['page_title']??'Kontakt'); site_header('kontakt'); $status=$_GET['status']??'';
?><main><?php site_hero(); ?><section class="contact-section"><div class="container"><div class="contact-card">
<div class="contact-photo" style="<?=h("--contact-image:url('".str_replace(["'",'"',')','\\'],'',(string)($c['image']??''))."')")?>" role="img" aria-label="<?=h($c['heading']??'Kontakt')?>"></div>
<div class="contact-form-col"><h2><?=h($c['heading']??'Kontakt')?></h2>
<?php if($status==='sent'): ?><div class="form-notice success"><?=h($c['success_message']??'')?></div><?php endif; ?>
<?php if($status==='error'): ?><div class="form-notice error"><?=h($c['error_message']??'')?></div><?php endif; ?>
<form action="/api/contact.php" method="post" accept-charset="UTF-8" data-track-form="contact">
<div class="form-grid">
<div class="full"><label for="name"><?=h($labels['name']??'Vorname & Nachname *')?></label><input id="name" name="name" autocomplete="name" required maxlength="120"></div>
<div class="full"><label for="email"><?=h($labels['email']??'Email *')?></label><input id="email" name="email" type="email" autocomplete="email" required maxlength="190"></div>
<div><label for="phone"><?=h($labels['phone']??'Telefon')?></label><input id="phone" name="phone" type="tel" autocomplete="tel" maxlength="60"<?= !empty($c['phone_required'])?' required':'' ?>></div>
<div><label for="topic"><?=h($labels['topic']??'Anliegen *')?></label><select id="topic" name="topic" required><option value="">Bitte auswählen</option><?php foreach(($c['topics']??[]) as $topic): ?><option value="<?=h($topic)?>"><?=h($topic)?></option><?php endforeach; ?></select></div>
<div class="full"><label for="message"><?=h($labels['message']??'Nachricht *')?></label><textarea id="message" name="message" required maxlength="5000"></textarea></div>
<div class="honeypot" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
<div class="full"><button class="contact-submit" data-track="contact_submit" type="submit"><?=h($labels['submit']??'Senden')?></button></div>
</div></form></div></div></div></section></main><?php site_footer(); ?></body></html>