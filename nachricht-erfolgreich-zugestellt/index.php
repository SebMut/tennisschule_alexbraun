<?php
require dirname(__DIR__) . '/_inc/site.php';
$d=site_data(); $s=$d['success']??[];
page_head($s['page_title']??'Nachricht erfolgreich zugestellt'); site_header();
?><main><?php site_hero(); ?><section class="section"><div class="container"><p><?=h($s['message']??'')?></p></div></section></main><?php site_footer(); ?></body></html>
