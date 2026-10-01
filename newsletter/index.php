<?php
require dirname(__DIR__) . '/_inc/site.php';
page_head('Anmeldung zum Newsletter', 'Willkommen beim Newsletter der Tennisschule Alex Braun. Über den Newsletter informiere ich vorab, wenn die Anmeldungen zu den jeweiligen Camps starten. Auch andere interessante News und Fakten werden über den Newsletter geteilt.');
site_header();
?>
<main>
<?php site_hero(); ?>
<section class="newsletter-intro">
  <div class="container newsletter-copy">
    <div class="newsletter-icon" aria-hidden="true">✉</div>
    <p class="newsletter-welcome">Willkommen beim Newsletter der Tennisschule Alex Braun.</p>
    <h5>Über den Newsletter informiere ich vorab, wenn die Anmeldungen zu den jeweiligen Camps starten.<br>Auch andere interessante News und Fakten werden über den Newsletter geteilt.</h5>
  </div>
</section>
<section class="contact-section">
  <div class="container">
    <div class="contact-card">
      <div class="contact-photo" role="img" aria-label="Alex Braun"></div>
      <div class="contact-form-col">
        <h2>Kontakt</h2>
        <form action="/api/contact.php" method="post" accept-charset="UTF-8">
          <div class="form-grid">
            <div class="full"><label for="name">Vorname &amp; Nachname *</label><input id="name" name="name" autocomplete="name" required maxlength="120"></div>
            <div><label for="subject">Betreff *</label><input id="subject" name="subject" required maxlength="160"></div>
            <div><label for="phone">Telefon *</label><input id="phone" name="phone" type="tel" autocomplete="tel" required maxlength="60"></div>
            <div class="full"><label for="email">Email *</label><input id="email" name="email" type="email" autocomplete="email" required maxlength="190"></div>
            <div class="full"><label for="message">Nachricht *</label><textarea id="message" name="message" required maxlength="5000"></textarea></div>
            <div class="honeypot" aria-hidden="true"><label>Falls Du menschlich bist, lasse dieses Feld leer.<input name="website" tabindex="-1" autocomplete="off"></label></div>
            <div class="full"><button class="contact-submit" type="submit">Senden</button></div>
          </div>
        </form>
        <div class="contact-social">
          <a href="tel:+491722104303" aria-label="Telefon"><?= social_icon('phone') ?></a>
          <a href="https://wa.me/+491722104303" aria-label="WhatsApp"><?= social_icon('whatsapp') ?></a>
          <a href="mailto:info@tennisschule-alexbraun.de" aria-label="E-Mail"><?= social_icon('mail') ?></a>
          <a href="https://www.instagram.com/tennisschule_alexbraun" target="_blank" rel="noopener" aria-label="Instagram"><?= social_icon('instagram') ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
</main>
<?php site_footer(); ?>
</body></html>
