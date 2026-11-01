<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data();
$s=$d['success']??[];
$heroImage=str_replace(["'",'"',')','\\'],'',(string)($d['site']['hero_image']??'/assets/media/header_bild.webp'));
page_head($s['page_title']??'Nachricht erfolgreich zugestellt', 'Danke für deine Nachricht an die Tennisschule Alex Braun. Wir melden uns bei dir.');
site_header();
?>
<main class="success-page">
  <section class="hero page-hero success-hero" style="<?=h("--hero-image:url('".$heroImage."')")?>" aria-labelledby="success-hero-heading">
    <div class="hero-overlay"></div>
    <div class="container success-hero-inner">
      <span class="success-hero-eyebrow"><span class="success-hero-dot" aria-hidden="true"></span> Kontakt zur Tennisschule</span>
      <h1 id="success-hero-heading">Nachricht erfolgreich gesendet!</h1>
      <p>Danke, dass du dich bei uns gemeldet hast.</p>
    </div>
  </section>

  <section class="success-section" aria-labelledby="success-title">
    <div class="container">
      <div class="success-card">
        <div class="success-icon" aria-hidden="true">
          <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="32" cy="32" r="30" stroke="currentColor" stroke-width="2" stroke-opacity=".24"/>
            <path d="M19 32.5 28.5 42 46 23" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <span class="success-kicker">Alles erledigt</span>
        <h2 id="success-title"><?=h($s['message']??'Vielen Dank für deine Nachricht.')?></h2>
        <p class="success-intro">Deine Anfrage ist bei uns angekommen. Wir kümmern uns darum und melden uns persönlich bei dir.</p>

        <div class="success-next">
          <div class="success-next-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2.5" y="5" width="19" height="14" rx="2"/>
              <path d="m3.5 7 8.5 6 8.5-6"/>
            </svg>
          </div>
          <div>
            <h3>So geht es weiter</h3>
            <p>Wir schauen uns dein Anliegen an und antworten dir per E-Mail. In deinem Postfach kannst du auch nach unserer automatischen Eingangsbestätigung schauen.</p>
          </div>
        </div>

        <div class="success-actions">
          <a class="success-action success-action-primary" href="/angebote/">
            Unsere Trainings entdecken
            <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
          </a>
          <a class="success-action success-action-secondary" href="/">
            Zur Startseite
          </a>
        </div>
        <p class="success-contact-note">Noch eine Frage? <a href="/kontakt/">Zur Kontaktseite</a> oder <a href="mailto:info@tennisschule-alexbraun.de">direkt eine E-Mail schreiben</a>.</p>
      </div>
      <p class="success-signoff">Tennisschule Alex Braun <span aria-hidden="true">·</span> Wir bleiben am Ball.</p>
    </div>
  </section>
</main>
<?php site_footer(); ?>
</body></html>
