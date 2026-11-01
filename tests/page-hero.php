<?php
declare(strict_types=1);
require dirname(__DIR__) . '/_inc/site.php';

function check_hero(bool $ok,string $message): void {
    if(!$ok){fwrite(STDERR,"Hero-Test fehlgeschlagen: ".$message."\n");exit(1);}
}

$d=site_data();
$pages=[
    '/angebote/' => (string)($d['offers']['page_title']??'Unser Angebot'),
    '/trainerteam/' => (string)($d['trainers']['page_title']??'Trainerteam'),
    '/standorte/' => (string)($d['locations']['page_title']??'Unsere Standorte'),
    '/tsv-feldkirchen/' => (string)($d['locations']['feldkirchen']['name']??'TSV Feldkirchen'),
    '/sv-heimstetten/' => (string)($d['locations']['heimstetten']['name']??'SV Heimstetten'),
    '/kontakt/' => (string)($d['contact']['page_title']??'Kontakt'),
    '/impressum-datenschutzerklaerung/' => (string)($d['legal']['page_title']??'Impressum & Datenschutzerklärung'),
    '/newsletter/' => (string)($d['newsletter']['page_title']??'Newsletter'),
    '/newsletter/abmelden/' => (string)($d['newsletter']['unsubscribe_page_title']??'Newsletter abmelden'),
];
foreach($pages as $path=>$title){
    $_SERVER['REQUEST_URI']=$path;
    check_hero(site_hero_page_title($d)===$title,"Seitenname für ".$path);
    ob_start();
    site_hero();
    $html=(string)ob_get_clean();
    check_hero(str_contains($html,'<span class="hero-title-desktop">'.h($title).'</span>'),"Desktop-Titel für ".$path);
    check_hero(str_contains($html,'<span class="hero-title-mobile">'.h($title).'</span>'),"Mobile-Titel für ".$path);
}
$_SERVER['REQUEST_URI']='/';
ob_start();site_hero(true);$home=(string)ob_get_clean();
check_hero(str_contains($home,h($d['site']['hero_title']??'Tennisschule Alex Braun')),"Startseite behält den Website-Namen");
$_SERVER['REQUEST_URI']='/nicht-vorhanden/';
http_response_code(404);
check_hero(site_hero_page_title($d)===(string)($d['not_found']['title']??'Seite nicht gefunden'),"404-Titel");
http_response_code(200);
ob_start();site_header('angebote');$header=(string)ob_get_clean();
check_hero((bool)preg_match('/data-track="nav_shop"[^>]*target="_blank"[^>]*rel="noopener noreferrer"/',$header),"Shop öffnet am Desktop einen neuen Tab");
check_hero((bool)preg_match('/<nav class="mobile-nav">.*?<a href="https:\/\/shop\.tennisschule-alexbraun\.de\/" target="_blank" rel="noopener noreferrer">/s',$header),"Shop öffnet mobil einen neuen Tab");
echo "Seitentitel und Shop-Link: Tests erfolgreich.\n";
