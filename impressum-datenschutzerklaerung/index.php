<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $l=$d['legal']??[];
page_head($l['page_title']??'Impressum & Datenschutzerklärung'); site_header();
?><main><?php site_hero(); ?><section class="section"><div class="container legal">
<h2 class="legal-main-title"><?=h($l['page_title']??'Impressum & Datenschutzerklärung')?></h2>
<p><?=hbr($l['intro']??'')?></p>
<?php foreach(($l['sections']??[]) as $section): ?><h2><?=h($section['title']??'')?></h2><p><?=hbr($section['body']??'')?></p><?php endforeach; ?>
</div></section></main><?php site_footer(); ?></body></html>
