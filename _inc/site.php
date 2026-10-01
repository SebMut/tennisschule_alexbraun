<?php
declare(strict_types=1);

function site_data(): array {
    static $data = null;
    if ($data !== null) return $data;
    $path = dirname(__DIR__) . '/data/site.json';
    $json = @file_get_contents($path);
    if ($json === false) return [];
    $decoded = json_decode($json, true);
    $data = is_array($decoded) ? $decoded : [];
    return $data;
}

function h(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function nav_active(string $active, string $name): string {
    return $active === $name ? ' active' : '';
}

function site_header(string $active = ''): void {
    $d = site_data();
    $logo = $d['site']['logo'] ?? '/assets/media/logo.png';
    echo '<header class="site-header" id="siteHeader">';
    echo '<div class="nav-shell container">';
    echo '<a class="brand" href="/" aria-label="Tennisschule Alex Braun"><img src="'.h($logo).'" alt="Tennisschule Alex Braun"></a>';
    echo '<nav class="main-nav" id="mainNav" aria-label="Hauptnavigation">';
    echo '<a class="'.trim(nav_active($active,'home')).'" href="/">Home</a>';
    echo '<a class="'.trim(nav_active($active,'angebote')).'" href="/angebote/">Angebote</a>';
    echo '<a class="'.trim(nav_active($active,'trainerteam')).'" href="/trainerteam/">Trainerteam</a>';
    echo '<div class="nav-dropdown'.nav_active($active,'standorte').'">';
    echo '<a href="/standorte/">Standorte <span class="nav-caret" aria-hidden="true">⌄</span></a>';
    echo '<div class="dropdown-menu"><a href="/tsv-feldkirchen/">TSV Feldkirchen</a><a href="/sv-heimstetten/">SV Heimstetten</a></div>';
    echo '</div>';
    echo '<a href="https://shop.tennisschule-alexbraun.de/">Shop</a>';
    echo '<a class="'.trim(nav_active($active,'kontakt')).'" href="/kontakt/">Kontakt</a>';
    echo '</nav>';
    echo '<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobileMenu" aria-label="Menü öffnen">';
    echo '<span></span><span></span><span></span></button>';
    echo '</div>';
    echo '<div class="mobile-overlay" id="mobileOverlay"></div>';
    echo '<aside class="mobile-menu" id="mobileMenu" aria-hidden="true">';
    echo '<div class="mobile-menu-head"><a class="mobile-brand" href="/"><img src="'.h($logo).'" alt="Tennisschule Alex Braun"></a><button class="mobile-close" type="button" aria-label="Menü schließen">×</button></div>';
    echo '<nav class="mobile-nav" aria-label="Mobile Navigation">';
    echo '<a href="/">Home</a><a href="/angebote/">Angebote</a><a href="/trainerteam/">Trainerteam</a>';
    echo '<details><summary>Standorte</summary><a href="/standorte/">Übersicht</a><a href="/tsv-feldkirchen/">TSV Feldkirchen</a><a href="/sv-heimstetten/">SV Heimstetten</a></details>';
    echo '<a href="https://shop.tennisschule-alexbraun.de/">Shop</a><a href="/kontakt/">Kontakt</a>';
    echo '</nav></aside>';
    echo '</header>';
}

function site_hero(bool $home = false): void {
    $d = site_data();
    $tagline = $d['site']['tagline'] ?? '';
    $copy = $d['site']['hero_copy'] ?? '';
    $heroImage = (string)($d['site']['hero_image'] ?? '/assets/media/header_bild.jpg');
    $heroImage = str_replace(["'", '"', ')', "\\"], '', $heroImage);
    $heroStyle = "--hero-image:url('" . $heroImage . "')";
    echo '<section class="hero '.($home ? 'home-hero' : 'page-hero').'" style="'.h($heroStyle).'">';
    echo '<div class="hero-overlay"></div><div class="container hero-content">';
    echo '<h1 class="hero-title"><span class="hero-title-desktop">Tennisschule Alex Braun</span><span class="hero-title-mobile">Tennisschule<br>Alex Braun</span></h1>';
    if ($home) {
        echo '<p class="hero-lead">'.h($tagline).'</p>';
        echo '<p class="hero-copy">'.h($copy).'</p>';
        echo '<div class="hero-actions"><a class="btn hero-btn hero-btn-orange" href="/angebote/">Anmeldung</a><a class="btn hero-btn hero-btn-white" href="/kontakt/">Kontakt</a></div>';
    }
    echo '</div></section>';
}

function social_icon(string $type): string {
    $icons = [
        'phone' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8c1.8 3.5 3.1 4.8 6.6 6.6l2.2-2.2c.3-.3.7-.4 1.1-.3 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.8 21 3 13.2 3 3.7c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.3 1.1l-2.2 2.2z"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0C5.6 0 .3 5.3.3 11.8c0 2.1.5 4.1 1.6 5.9L.2 24l6.4-1.7a11.8 11.8 0 0 0 5.6 1.4h.1c6.5 0 11.8-5.3 11.8-11.8 0-3.2-1.3-6.2-3.6-8.4zm-8.3 18.2c-1.7 0-3.5-.5-5-1.4l-.4-.2-3.8 1 1-3.7-.2-.4a9.8 9.8 0 1 1 8.4 4.7zm5.4-7.3c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-.9 1.2-.2.2-.3.2-.6.1-1.8-.9-3-1.6-4.2-3.7-.3-.5.3-.5.9-1.6.1-.2 0-.4-.1-.6l-1-2.3c-.3-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.5c.2.2 2.4 3.7 5.8 5.2.8.4 1.4.6 1.9.7.8.3 1.6.2 2.2.1.7-.1 1.8-.7 2.1-1.4.3-.7.3-1.3.2-1.4-.1-.2-.3-.3-.6-.4z"/></svg>',
        'mail' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h16a2 2 0 0 0 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7zm10.5 1.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>'
    ];
    return $icons[$type] ?? '';
}

function site_footer(): void {
    echo '<footer class="site-footer"><div class="container footer-grid">';
    echo '<div class="footer-left"><a class="footer-name" href="/">Tennisschule Alex Braun</a>';
    echo '<div class="footer-social">';
    echo '<a href="tel:+491722104303" aria-label="Telefon">'.social_icon('phone').'</a>';
    echo '<a href="https://wa.me/+491722104303" aria-label="WhatsApp">'.social_icon('whatsapp').'</a>';
    echo '<a href="mailto:info@tennisschule-alexbraun.de" aria-label="E-Mail">'.social_icon('mail').'</a>';
    echo '<a href="https://www.instagram.com/tennisschule_alexbraun" aria-label="Instagram" target="_blank" rel="noopener">'.social_icon('instagram').'</a>';
    echo '</div></div>';
    echo '<div class="footer-right"><a href="/impressum-datenschutzerklaerung/">Impressum &amp; Datenschutzerklärung</a>';
    echo '<p>© '.date('Y').' Tennisschule Alex Braun.</p></div>';
    echo '</div></footer><script src="/assets/site.js"></script>';
}

function page_head(string $title, string $description = '', bool $mapbox = false): void {
    if ($description === '') $description = $title . ' - Tennisschule Alex Braun';
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    $isStaging = $host === 'test.tennisschule-alexbraun.de' || str_starts_with($host, 'test.tennisschule-alexbraun.de:');
    if ($isStaging && !headers_sent()) {
        header('X-Robots-Tag: noindex, nofollow, noarchive', true);
    }
    echo '<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>'.h($title).' - Tennisschule Alex Braun</title><meta name="description" content="'.h($description).'">';
    if ($isStaging) echo '<meta name="robots" content="noindex,nofollow,noarchive">';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&family=Open+Sans:wght@400;600&family=Syne:wght@400;600&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="/assets/style.css"><link rel="stylesheet" href="/assets/trainings-anmeldung.css">';
    if ($mapbox) {
        echo '<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css">';
        echo '<script defer src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>';
    }
    echo '</head><body>';
}
