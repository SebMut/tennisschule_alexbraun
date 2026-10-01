<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $n=$d['newsletter']??[];
page_head($n['page_title']??'Newsletter'); site_header();
?><main><?php site_hero(); ?>
<section class="newsletter-signup-section"><div class="container narrow">
  <div class="newsletter-signup-card">
    <div class="newsletter-signup-copy">
      <div class="newsletter-icon" aria-hidden="true">✉</div>
      <p class="newsletter-welcome"><?=h($n['welcome']??'')?></p>
      <h2><?=h($n['heading']??'Newsletter abonnieren')?></h2>
      <p><?=h($n['signup_text']??$n['text']??'')?></p>
    </div>

    <?php if(!empty($n['enabled'])): ?>
    <form id="newsletterSignupForm" class="newsletter-signup-form" action="/api/newsletter-signup.php" method="post">
      <label for="newsletter-name"><?=h($n['name_label']??'Name (optional)')?></label>
      <input id="newsletter-name" name="name" autocomplete="name" maxlength="120">

      <label for="newsletter-email"><?=h($n['email_label']??'E-Mail-Adresse *')?></label>
      <input id="newsletter-email" name="email" type="email" autocomplete="email" required maxlength="190">

      <label class="newsletter-consent">
        <input name="consent" value="yes" type="checkbox" required>
        <span><?=h($n['consent_label']??'Ich möchte den Newsletter erhalten.')?> <a href="/impressum-datenschutzerklaerung/" target="_blank" rel="noopener"><?=h($n['privacy_label']??'Datenschutzerklärung')?></a></span>
      </label>

      <div class="honeypot" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
      <button class="kubio-btn newsletter-submit" data-track="newsletter_signup" type="submit"><?=h($n['submit_label']??'Newsletter abonnieren')?></button>
      <div id="newsletterMessage" class="newsletter-message" role="status" aria-live="polite"></div>
    </form>
    <?php else: ?>
      <div class="newsletter-message">Die Newsletter-Anmeldung ist derzeit deaktiviert.</div>
    <?php endif; ?>
  </div>
</div></section>
</main><?php site_footer(); ?></body></html>
