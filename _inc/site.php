<?php
declare(strict_types=1);

function site_data(): array {
    static $data = null;
    if ($data !== null) return $data;
    $json = @file_get_contents(dirname(__DIR__) . '/data/site.json');
    $decoded = $json === false ? null : json_decode($json, true);
    return $data = is_array($decoded) ? $decoded : [];
}
function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function hbr(?string $v): string { return nl2br(h($v), false); }
function local_server_config(): array {
    static $config = null;
    if ($config !== null) return $config;
    $path = dirname(__DIR__) . '/config.local.php';
    if (!is_file($path)) return $config = [];
    $loaded = require $path;
    return $config = is_array($loaded) ? $loaded : [];
}
function mapbox_public_token(): string { return (string)((local_server_config()['mapbox']['token'] ?? '')); }
function nav_active(string $active, string $name): string { return $active === $name ? ' active' : ''; }

function site_header(string $active = ''): void {
    $d=site_data(); $s=$d['site']??[]; $n=$s['navigation']??[]; $loc=$d['locations']??[];
    $logo=$s['logo']??'/assets/media/logo.png'; $name=$s['name']??'Tennisschule Alex Braun';
    $f=$loc['feldkirchen']['name']??'TSV Feldkirchen'; $h=$loc['heimstetten']['name']??'SV Heimstetten';
    $shopUrl=$n['shop_url']??'https://shop.tennisschule-alexbraun.de/';
    echo '<header class="site-header" id="siteHeader"><div class="nav-shell container">';
    echo '<a class="brand" data-track="nav_logo" href="/" aria-label="'.h($name).'"><img src="'.h($logo).'" alt="'.h($name).'"></a>';
    echo '<nav class="main-nav" id="mainNav" aria-label="Hauptnavigation">';
    echo '<a data-track="nav_home" class="'.trim(nav_active($active,'home')).'" href="/">'.h($n['home']??'Home').'</a>';
    echo '<a data-track="nav_offers" class="'.trim(nav_active($active,'angebote')).'" href="/angebote/">'.h($n['offers']??'Angebote').'</a>';
    echo '<a data-track="nav_trainers" class="'.trim(nav_active($active,'trainerteam')).'" href="/trainerteam/">'.h($n['trainers']??'Trainerteam').'</a>';
    echo '<div class="nav-dropdown'.nav_active($active,'standorte').'"><a data-track="nav_locations" href="/standorte/">'.h($n['locations']??'Standorte').' <span class="nav-caret" aria-hidden="true">⌄</span></a>';
    echo '<div class="dropdown-menu"><a data-track="nav_location_feldkirchen" href="/tsv-feldkirchen/">'.h($f).'</a><a data-track="nav_location_heimstetten" href="/sv-heimstetten/">'.h($h).'</a></div></div>';
    echo '<a data-track="nav_shop" href="'.h($shopUrl).'">'.h($n['shop']??'Shop').'</a>';
    echo '<a data-track="nav_contact" class="'.trim(nav_active($active,'kontakt')).'" href="/kontakt/">'.h($n['contact']??'Kontakt').'</a>';
    echo '</nav><button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobileMenu" aria-label="Menü öffnen"><span></span><span></span><span></span></button></div>';
    echo '<div class="mobile-overlay" id="mobileOverlay"></div><aside class="mobile-menu" id="mobileMenu" aria-hidden="true">';
    echo '<div class="mobile-menu-head"><a class="mobile-brand" href="/"><img src="'.h($logo).'" alt="'.h($name).'"></a><button class="mobile-close" type="button" aria-label="Menü schließen">×</button></div>';
    echo '<nav class="mobile-nav"><a href="/">'.h($n['home']??'Home').'</a><a href="/angebote/">'.h($n['offers']??'Angebote').'</a><a href="/trainerteam/">'.h($n['trainers']??'Trainerteam').'</a>';
    echo '<details><summary>'.h($n['locations']??'Standorte').'</summary><a href="/standorte/">'.h($n['locations_overview']??'Übersicht').'</a><a href="/tsv-feldkirchen/">'.h($f).'</a><a href="/sv-heimstetten/">'.h($h).'</a></details>';
    echo '<a href="'.h($shopUrl).'">'.h($n['shop']??'Shop').'</a><a href="/kontakt/">'.h($n['contact']??'Kontakt').'</a></nav></aside></header>';
}

function site_hero(bool $home=false): void {
    $d=site_data(); $s=$d['site']??[];
    $img=str_replace(["'",'"',')','\\'],'',(string)($s['hero_image']??'/assets/media/header_bild.jpg'));
    echo '<section class="hero '.($home?'home-hero':'page-hero').'" style="'.h("--hero-image:url('".$img."')").'"><div class="hero-overlay"></div><div class="container hero-content">';
    echo '<h1 class="hero-title"><span class="hero-title-desktop">'.h($s['hero_title']??'Tennisschule Alex Braun').'</span><span class="hero-title-mobile">'.hbr($s['hero_title_mobile']??"Tennisschule\nAlex Braun").'</span></h1>';
    if($home){
      echo '<p class="hero-lead">'.h($s['tagline']??'').'</p><p class="hero-copy">'.h($s['hero_copy']??'').'</p><div class="hero-actions">';
      echo '<a data-track="hero_primary" class="btn hero-btn hero-btn-orange" href="'.h($s['hero_primary_url']??'/angebote/').'">'.h($s['hero_primary_button']??'Anmeldung').'</a>';
      echo '<a data-track="hero_secondary" class="btn hero-btn hero-btn-white" href="'.h($s['hero_secondary_url']??'/kontakt/').'">'.h($s['hero_secondary_button']??'Kontakt').'</a></div>';
    }
    echo '</div></section>';
}

function social_icon(string $type): string {
    $i=[
      'phone'=>'<svg viewBox="0 0 24 24"><path d="M6.6 10.8c1.8 3.5 3.1 4.8 6.6 6.6l2.2-2.2c.3-.3.7-.4 1.1-.3 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.8 21 3 13.2 3 3.7c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.3 1.1l-2.2 2.2z"/></svg>',
      'whatsapp'=>'<svg viewBox="0 0 24 24"><path d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0C5.6 0 .3 5.3.3 11.8c0 2.1.5 4.1 1.6 5.9L.2 24l6.4-1.7a11.8 11.8 0 0 0 5.6 1.4h.1c6.5 0 11.8-5.3 11.8-11.8 0-3.2-1.3-6.2-3.6-8.4z"/></svg>',
      'mail'=>'<svg viewBox="0 0 24 24"><path d="M20 4H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h16a2 2 0 0 0 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>',
      'instagram'=>'<svg viewBox="0 0 24 24"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm5 5a5 5 0 1 1 0 10 5 5 0 0 1 0-10z"/></svg>'
    ]; return $i[$type]??'';
}

function site_footer(): void {
    $d=site_data(); $f=$d['site']['footer']??[];
    $phone=$f['phone']??'+49 172 2104 303'; $email=$f['email']??'info@tennisschule-alexbraun.de';
    echo '<footer class="site-footer"><div class="container footer-grid"><div class="footer-left">';
    echo '<a class="footer-name" href="/">'.h($f['name']??'Tennisschule Alex Braun').'</a><div class="footer-social">';
    echo '<a data-track="footer_phone" href="tel:'.h(preg_replace('/\s+/','',$phone)).'" aria-label="Telefon">'.social_icon('phone').'</a>';
    echo '<a data-track="footer_whatsapp" href="https://wa.me/'.h(preg_replace('/\D+/','',$phone)).'" aria-label="WhatsApp">'.social_icon('whatsapp').'</a>';
    echo '<a data-track="footer_email" href="mailto:'.h($email).'" aria-label="E-Mail">'.social_icon('mail').'</a>';
    echo '<a data-track="footer_instagram" href="'.h($f['instagram']??'').'" target="_blank" rel="noopener" aria-label="Instagram">'.social_icon('instagram').'</a></div></div>';
    echo '<div class="footer-right"><a data-track="footer_legal" href="/impressum-datenschutzerklaerung/">'.h($f['legal_label']??'Impressum & Datenschutzerklärung').'</a>';
    echo '<p>© '.date('Y').' '.h($f['copyright_name']??'Tennisschule Alex Braun').'.</p></div></div></footer><script src="/assets/site.js"></script>';
}

function page_head(string $title,string $description='',bool $mapbox=false): void {
    if($description==='') $description=$title.' - Tennisschule Alex Braun';
    $host=strtolower((string)($_SERVER['HTTP_HOST']??'')); $staging=str_starts_with($host,'test.tennisschule-alexbraun.de');
    if($staging&&!headers_sent()) header('X-Robots-Tag: noindex, nofollow, noarchive',true);
    echo '<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.h($title).' - Tennisschule Alex Braun</title><meta name="description" content="'.h($description).'">';
    if($staging) echo '<meta name="robots" content="noindex,nofollow,noarchive">';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&family=Open+Sans:wght@400;600&family=Syne:wght@400;600&display=swap" rel="stylesheet"><link rel="stylesheet" href="/assets/style.css"><link rel="stylesheet" href="/assets/trainings-anmeldung.css">';
    if($mapbox) echo '<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css"><script defer src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>';
    echo '</head><body>';
}
