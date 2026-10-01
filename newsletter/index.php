<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $n=$d['newsletter']??[]; $c=$d['contact']??[]; $labels=$c['labels']??[];
page_head($n['page_title']??'Newsletter'); site_header();
?><main><?php site_hero(); ?>
<section class="newsletter-intro"><div class="container newsletter-copy"><div class="newsletter-icon" aria-hidden="true">✉</div>
<p class="newsletter-welcome"><?=h($n['welcome']??'')?></p><h5><?=hbr($n['text']??'')?></h5></div></section>
<section class="contact-section"><div class="container"><div class="contact-card">
<div class="contact-photo" style="<?=h("--contact-image:url('".str_replace(["'",'"',')','\\'],'',(string)($c['image']??''))."')")?>" role="img"></div>
<div class="contact-form-col"><h2><?=h($c['heading']??'Kontakt')?></h2><form action="/api/contact.php" method="post" accept-charset="UTF-8" data-track-form="newsletter-contact">
<div class="form-grid">
<div class="full"><label for="name"><?=h($labels['name']??'Vorname & Nachname *')?></label><input id="name" name="name" required></div>
<div><label for="subject"><?=h($labels['subject']??'Betreff *')?></label><input id="subject" name="subject" required></div>
<div><label for="phone"><?=h($labels['phone']??'Telefon *')?></label><input id="phone" name="phone" type="tel" required></div>
<div class="full"><label for="email"><?=h($labels['email']??'Email *')?></label><input id="email" name="email" type="email" required></div>
<div class="full"><label for="message"><?=h($labels['message']??'Nachricht *')?></label><textarea id="message" name="message" required></textarea></div>
<div class="honeypot"><input name="website" tabindex="-1" autocomplete="off"></div>
<div class="full"><button class="contact-submit" data-track="newsletter_submit" type="submit"><?=h($labels['submit']??'Senden')?></button></div>
</div></form></div></div></div></section>
</main><?php site_footer(); ?></body></html>
