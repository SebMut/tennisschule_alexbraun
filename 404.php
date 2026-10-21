<?php
http_response_code(404);
require __DIR__ . '/_inc/site.php';
$d=site_data(); $n=$d['not_found']??[];
page_head($n['title']??'Seite nicht gefunden'); site_header();
?><main><?php site_hero(); ?>
<section class="error404-fun" data-error404>
  <div class="container narrow">

    <div class="error404-stage" aria-hidden="true">
      <div class="error404-scene error404-scene-net" data-error404-scene="net">
        <div class="error404-score">4<span>0</span>4</div>
        <div class="error404-net">
          <span class="error404-net-line"></span>
        </div>
        <div class="error404-ball error404-ball-net"></div>
      </div>

      <div class="error404-scene error404-scene-racket" data-error404-scene="racket">
        <div class="error404-score error404-score-racket">
          <span class="error404-digit digit-one">4</span>
          <span class="error404-digit digit-zero">0</span>
          <span class="error404-digit digit-three">4</span>
        </div>
        <div class="error404-racket">
          <div class="error404-racket-head"></div>
          <div class="error404-racket-handle"></div>
        </div>
        <div class="error404-ball error404-ball-racket"></div>
      </div>

      <div class="error404-scene error404-scene-court" data-error404-scene="court">
        <div class="mini-court">
          <div class="mini-court-line mini-court-line-v"></div>
          <div class="mini-court-line mini-court-line-h"></div>
          <div class="mini-court-net"></div>
          <div class="error404-ball error404-ball-court"></div>
        </div>
        <div class="error404-court-number">404</div>
      </div>

      <div class="error404-scene error404-scene-numbercourt" data-error404-scene="numbercourt">
        <div class="numbercourt-wrap">
          <div class="numbercourt-digit numbercourt-four">
            <span class="numbercourt-line a"></span>
            <span class="numbercourt-line b"></span>
            <span class="numbercourt-line c"></span>
          </div>
          <div class="numbercourt-zero">
            <span class="numbercourt-baseline top"></span>
            <span class="numbercourt-baseline bottom"></span>
            <span class="numbercourt-sideline left"></span>
            <span class="numbercourt-sideline right"></span>
            <span class="numbercourt-netline"></span>
            <div class="error404-ball error404-ball-numbercourt"></div>
          </div>
          <div class="numbercourt-digit numbercourt-four numbercourt-four-last">
            <span class="numbercourt-line a"></span>
            <span class="numbercourt-line b"></span>
            <span class="numbercourt-line c"></span>
          </div>
        </div>
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
