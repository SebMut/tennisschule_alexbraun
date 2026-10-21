<?php
http_response_code(404);
require __DIR__ . '/_inc/site.php';
$d=site_data(); $n=$d['not_found']??[];
page_head($n['title']??'Seite nicht gefunden'); site_header();
?><main><?php site_hero(); ?>
<section class="error404-fun" data-error404>
  <div class="container narrow">
    <div class="error404-stage" aria-hidden="true">

      <!-- 1: Ball trifft korrekt quer gespanntes Netz -->
      <div class="error404-scene scene-net" data-error404-scene="net">
        <svg class="error404-svg" viewBox="0 0 760 300" role="presentation">
          <rect x="0" y="0" width="760" height="300" rx="18" class="scene-bg"/>
          <text x="380" y="176" text-anchor="middle" class="ghost-404">404</text>

          <g class="court-topdown">
            <rect x="218" y="24" width="324" height="252" rx="5" class="court-surface"/>
            <rect x="235" y="38" width="290" height="224" class="court-outline"/>
            <line x1="235" y1="150" x2="525" y2="150" class="court-net-base"/>
            <line x1="272" y1="38" x2="272" y2="262" class="court-line"/>
            <line x1="488" y1="38" x2="488" y2="262" class="court-line"/>
            <line x1="272" y1="95" x2="488" y2="95" class="court-line"/>
            <line x1="272" y1="205" x2="488" y2="205" class="court-line"/>
            <line x1="380" y1="95" x2="380" y2="205" class="court-line"/>
          </g>

          <g class="net-assembly net-hit">
            <line x1="205" y1="150" x2="555" y2="150" class="net-tape"/>
            <line x1="205" y1="139" x2="205" y2="161" class="net-post"/>
            <line x1="555" y1="139" x2="555" y2="161" class="net-post"/>
            <g class="net-mesh">
              <line x1="220" y1="145" x2="220" y2="155"/><line x1="240" y1="145" x2="240" y2="155"/>
              <line x1="260" y1="145" x2="260" y2="155"/><line x1="280" y1="145" x2="280" y2="155"/>
              <line x1="300" y1="145" x2="300" y2="155"/><line x1="320" y1="145" x2="320" y2="155"/>
              <line x1="340" y1="145" x2="340" y2="155"/><line x1="360" y1="145" x2="360" y2="155"/>
              <line x1="380" y1="145" x2="380" y2="155"/><line x1="400" y1="145" x2="400" y2="155"/>
              <line x1="420" y1="145" x2="420" y2="155"/><line x1="440" y1="145" x2="440" y2="155"/>
              <line x1="460" y1="145" x2="460" y2="155"/><line x1="480" y1="145" x2="480" y2="155"/>
              <line x1="500" y1="145" x2="500" y2="155"/><line x1="520" y1="145" x2="520" y2="155"/>
              <line x1="540" y1="145" x2="540" y2="155"/>
            </g>
          </g>

          <g class="ball ball-net">
            <circle cx="380" cy="0" r="15" class="ball-fill"/>
            <path d="M369 -10 Q380 0 369 10 M391 -10 Q380 0 391 10" class="ball-seam"/>
          </g>
        </svg>
      </div>

      <!-- 2: Anatomisch sauberer Schläger schlägt die letzte 4 um -->
      <div class="error404-scene scene-racket" data-error404-scene="racket">
        <svg class="error404-svg" viewBox="0 0 760 300" role="presentation">
          <rect x="0" y="0" width="760" height="300" rx="18" class="scene-bg"/>
          <g class="racket-score">
            <text x="285" y="195" text-anchor="middle" class="solid-404 digit-left">4</text>
            <text x="380" y="195" text-anchor="middle" class="solid-404 digit-zero">0</text>
            <text x="475" y="195" text-anchor="middle" class="solid-404 digit-last">4</text>
          </g>

          <g transform="translate(610 164)">
          <g class="tennis-racket-svg">
            <ellipse cx="0" cy="-80" rx="49" ry="63" class="racket-frame"/>
            <g class="racket-strings">
              <line x1="-32" y1="-122" x2="-32" y2="-38"/><line x1="-16" y1="-137" x2="-16" y2="-23"/>
              <line x1="0" y1="-143" x2="0" y2="-17"/><line x1="16" y1="-137" x2="16" y2="-23"/>
              <line x1="32" y1="-122" x2="32" y2="-38"/>
              <line x1="-42" y1="-111" x2="42" y2="-111"/><line x1="-48" y1="-95" x2="48" y2="-95"/>
              <line x1="-49" y1="-79" x2="49" y2="-79"/><line x1="-46" y1="-63" x2="46" y2="-63"/>
              <line x1="-37" y1="-47" x2="37" y2="-47"/>
            </g>
            <path d="M-28 -28 L-10 8 M28 -28 L10 8" class="racket-throat"/>
            <line x1="0" y1="6" x2="0" y2="58" class="racket-shaft"/>
            <rect x="-10" y="54" width="20" height="72" rx="7" class="racket-grip"/>
            <line x1="-8" y1="68" x2="8" y2="62" class="grip-wrap"/>
            <line x1="-8" y1="83" x2="8" y2="77" class="grip-wrap"/>
            <line x1="-8" y1="98" x2="8" y2="92" class="grip-wrap"/>
            <line x1="-8" y1="113" x2="8" y2="107" class="grip-wrap"/>
          </g>
          </g>

          <path class="impact-star" d="M515 122 l10 -14 3 16 16 -7 -9 14 15 7 -17 1 5 16 -13 -11 -10 13 2 -17 -17 3 14 -10 -10 -13 16 6z"/>
        </svg>
      </div>

      <!-- 3: Vollständiger Court, Netz quer, Ball springt plausibel über beide Platzhälften -->
      <div class="error404-scene scene-court" data-error404-scene="court">
        <svg class="error404-svg" viewBox="0 0 760 300" role="presentation">
          <rect x="0" y="0" width="760" height="300" rx="18" class="scene-bg"/>
          <g class="mini-court-svg">
            <rect x="145" y="22" width="470" height="256" rx="7" class="court-surface"/>
            <rect x="165" y="38" width="430" height="224" class="court-outline"/>
            <line x1="225" y1="38" x2="225" y2="262" class="court-line"/>
            <line x1="535" y1="38" x2="535" y2="262" class="court-line"/>
            <line x1="225" y1="95" x2="535" y2="95" class="court-line"/>
            <line x1="225" y1="205" x2="535" y2="205" class="court-line"/>
            <line x1="380" y1="95" x2="380" y2="205" class="court-line"/>
            <line x1="138" y1="150" x2="622" y2="150" class="court-net-base"/>
            <line x1="138" y1="139" x2="138" y2="161" class="net-post"/>
            <line x1="622" y1="139" x2="622" y2="161" class="net-post"/>
          </g>
          <text x="380" y="190" text-anchor="middle" class="court-watermark">404</text>
          <circle cx="315" cy="220" r="18" class="bounce-ring bounce-ring-one"/>
          <circle cx="450" cy="83" r="18" class="bounce-ring bounce-ring-two"/>

          <g class="ball ball-court">
            <ellipse cx="0" cy="18" rx="16" ry="5" class="ball-shadow-svg"/>
            <circle cx="0" cy="0" r="14" class="ball-fill"/>
            <path d="M-10 -9 Q0 0 -10 9 M10 -9 Q0 0 10 9" class="ball-seam"/>
          </g>
        </svg>
      </div>

      <!-- 4: 404 selbst ist Spielfeld; die 0 ist ein vollständiger Tennisplatz -->
      <div class="error404-scene scene-numbercourt" data-error404-scene="numbercourt">
        <svg class="error404-svg" viewBox="0 0 760 300" role="presentation">
          <rect x="0" y="0" width="760" height="300" rx="18" class="scene-bg"/>

          <g class="numbercourt-four left-four">
            <path d="M150 65 L92 181 L205 181 M181 65 L181 230" class="numbercourt-stroke"/>
          </g>

          <g class="zero-court">
            <rect x="280" y="38" width="200" height="224" rx="34" class="zero-frame"/>
            <rect x="302" y="56" width="156" height="188" rx="4" class="zero-court-surface"/>
            <rect x="315" y="70" width="130" height="160" class="zero-court-outline"/>
            <line x1="333" y1="70" x2="333" y2="230" class="zero-court-line"/>
            <line x1="427" y1="70" x2="427" y2="230" class="zero-court-line"/>
            <line x1="333" y1="110" x2="427" y2="110" class="zero-court-line"/>
            <line x1="333" y1="190" x2="427" y2="190" class="zero-court-line"/>
            <line x1="380" y1="110" x2="380" y2="190" class="zero-court-line"/>
            <line x1="295" y1="150" x2="465" y2="150" class="zero-net"/>
            <circle cx="380" cy="92" r="10" class="ball-fill ball-numbercourt"/>
          </g>

          <g class="numbercourt-four right-four">
            <path d="M610 65 L552 181 L665 181 M641 65 L641 230" class="numbercourt-stroke"/>
          </g>
        </svg>
      </div>

    </div>

    <div class="error404-copy">
      <h2><?=h($n['title']??'Seite nicht gefunden')?></h2>
      <p><?=h($n['text']??'Ups – dieser Ball ist im Aus gelandet.')?></p>
      <div class="button-row error404-actions">
        <a class="kubio-btn" data-track="404_home" href="<?=h($n['primary_url']??'/')?>"><?=h($n['primary_label']??'Zur Startseite')?></a>
        <a class="kubio-btn error404-secondary" data-track="404_offers" href="<?=h($n['secondary_url']??'/angebote/')?>"><?=h($n['secondary_label']??'Zu den Angeboten')?></a>
      </div>
    </div>
  </div>
</section>
</main><?php site_footer(); ?></body></html>
