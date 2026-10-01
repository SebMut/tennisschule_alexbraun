<?php
require __DIR__ . '/_inc/site.php';
$d = site_data();
page_head('Home', 'Seit Mai 2023 leitet die Tennisschule Alex Braun das Training beim TSV Feldkirchen. Ein motiviertes Team fördert Talente, Leistungssportler & den Breitensport.');
site_header('home');
?>
<main>
<?php site_hero(true); ?>
<div id="content-start"></div>

<section class="section about-section">
  <div class="container about-grid">
    <div class="about-copy">
      <h2><?= h($d['home']['about_title'] ?? 'Über uns') ?></h2>
      <?php foreach (($d['home']['about_paragraphs'] ?? []) as $i => $p): ?>
        <p><?php
          $safe = h($p);
          if ($i === 0) {
            $safe = str_replace(
              ['TSV Feldkirchen','SV Heimstetten'],
              ['<a href="/tsv-feldkirchen/">TSV Feldkirchen</a>','<a href="/sv-heimstetten/">SV Heimstetten</a>'],
              $safe
            );
          }
          echo $safe;
        ?></p>
      <?php endforeach; ?>
    </div>
    <div class="kubio-shadow-image">
      <img src="<?= h($d['home']['about_image'] ?? '/assets/media/startseite_1.jpg') ?>" alt="Alex">
    </div>
  </div>
</section>

<section class="offers-home">
  <div class="container">
    <div class="section-heading">
      <h2><?= h($d['home']['offers_title'] ?? 'Unser Angebot') ?></h2>
      <p><?= h($d['home']['offers_intro'] ?? '') ?></p>
    </div>
    <div class="cards three">
      <?php foreach (($d['offers']['items'] ?? []) as $offer): ?>
      <article class="offer-card">
        <img src="<?= h($offer['image'] ?? '') ?>" alt="<?= h($offer['title'] ?? '') ?>">
        <div class="offer-card-body">
          <h3><?= h($offer['title'] ?? '') ?></h3>
          <p><?= h($offer['home_text'] ?? '') ?></p>
          <a class="text-link" href="/angebote/">Zur Anmeldung</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="team-teaser">
  <div class="container">
    <div class="section-heading">
      <h2><?= h($d['home']['team_title'] ?? 'Unser Trainerteam') ?></h2>
      <p><?= h($d['home']['team_intro'] ?? '') ?></p>
    </div>
    <div class="team-visual">
      <div class="team-visual-action"><a class="kubio-btn" href="/trainerteam/">Zur TrainerÜbersicht</a></div>
    </div>
  </div>
</section>

<section class="home-locations-heading">
  <div class="container"><h2>Unsere Standorte</h2></div>
</section>
<section class="home-locations">
  <div class="container home-locations-grid">
    <?php $f=$d['locations']['feldkirchen'] ?? []; $h=$d['locations']['heimstetten'] ?? []; ?>
    <article class="home-location">
      <h2>TSV Feldkirchen</h2>
      <p>Der <a href="/tsv-feldkirchen/">TSV Feldkirchen</a> ist ein traditionsreicher Sportverein im Münchner Osten und verfügt über eine moderne Tennisanlage mit 8 Plätzen. Hier können Tennisspieler aller Altersklassen und Spielstärken ihrem Sport nachgehen und von einem breiten Angebot profitieren. Ob im Freizeit- oder Mannschaftsbereich – Training, Turniere und Vereinsleben werden aktiv gefördert. Eine offene, herzliche Vereinsatmosphäre sorgt dafür, dass sich neue wie langjährige Mitglieder gleichermaßen wohlfühlen.</p>
    </article>
    <article class="home-location">
      <h2>SV Heimstetten</h2>
      <p>Seit 1970 ist die Tennisabteilung des <a href="/sv-heimstetten/">SV Heimstetten</a> eine feste Größe im Vereinsleben und verfügt über eine moderne Anlage mit optimalen Trainings- und Wettkampfbedingungen. Hier finden Spielerinnen und Spieler aller Alters- und Leistungsstufen – vom Nachwuchs bis zum ambitionierten Mannschaftsspieler – ein vielseitiges Angebot. Engagierte Vereinsarbeit, Fairplay und eine starke Gemeinschaft machen die Tennisabteilung des SV Heimstetten zu einem beliebten Treffpunkt für Tennisbegeisterte in der Region.</p>
    </article>
  </div>
</section>
<section class="home-map-wrap">
  <div class="container">
    <div class="map-shell">
      <div class="map-consent">
        <p>Google Maps wird erst nach deiner Zustimmung geladen. Dabei können Daten an Google übertragen werden.</p>
        <button type="button" class="kubio-btn" data-load-map>Karte laden</button>
      </div>
      <iframe class="map-frame" loading="lazy" referrerpolicy="no-referrer-when-downgrade" data-map-src="https://maps.google.com/maps?iwloc=near&amp;output=embed&amp;q=Tennisschule+Alex+Braun+M%C3%BCnchen&amp;z=10" title="Standorte Tennisschule Alex Braun" hidden></iframe>
    </div>
  </div>
</section>

<div class="wp-popup-overlay" id="winterPopup" aria-hidden="true">
  <div class="wp-popup" role="dialog" aria-modal="true" aria-label="Wintertennistraining">
    <button class="wp-popup-close" type="button" aria-label="Schließen">X</button>
    <a href="/angebote/"><img src="/assets/media/popup_wintertraining_2026_2027-2.png" alt="Wintertennistraining"></a>
  </div>
</div>
</main>
<?php site_footer(); ?>
</body></html>
