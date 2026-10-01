<?php
require dirname(__DIR__) . '/_inc/site.php';
page_head('Kontakt');
site_header('kontakt');
$status = $_GET['status'] ?? '';
?>
<main>
<?php site_hero(); ?>
<section class="section"><div class="container narrow">
<h2>Kontakt</h2>
<?php if ($status === 'sent'): ?><div class="form-notice success">Vielen Dank. Deine Nachricht wurde versendet.</div><?php endif; ?>
<?php if ($status === 'error'): ?><div class="form-notice error">Die Nachricht konnte nicht versendet werden. Bitte versuche es erneut oder schreibe direkt an info@tennisschule-alexbraun.de.</div><?php endif; ?>
<form action="/api/contact.php" method="post" accept-charset="UTF-8">
<div class="form-grid">
<div class="full"><label for="name">Vorname &amp; Nachname *</label><input id="name" name="name" autocomplete="name" required maxlength="120"></div>
<div><label for="subject">Betreff *</label><input id="subject" name="subject" required maxlength="160"></div>
<div><label for="phone">Telefon *</label><input id="phone" name="phone" type="tel" autocomplete="tel" required maxlength="60"></div>
<div class="full"><label for="email">Email *</label><input id="email" name="email" type="email" autocomplete="email" required maxlength="190"></div>
<div class="full"><label for="message">Nachricht *</label><textarea id="message" name="message" required maxlength="5000"></textarea></div>
<div class="honeypot" aria-hidden="true"><label>Falls Du menschlich bist, lasse dieses Feld leer.<input name="website" tabindex="-1" autocomplete="off"></label></div>
<div class="full"><button class="btn btn-blue" type="submit">Senden</button></div>
</div>
</form>
</div></section>
</main>
<?php site_footer(); ?>
</body></html>
