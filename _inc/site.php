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
function site_header(string $active = ''): void {
    $d = site_data();
    $logo = $d['site']['logo'] ?? '/assets/media/logo.png';
    $a = fn(string $name): string => $active === $name ? ' class="active"' : '';
    echo '<header class="site-header"><div class="container header-inner">';
    echo '<a class="brand" href="/" aria-label="Tennisschule Alex Braun"><img src="'.h($logo).'" alt="Tennisschule Alex Braun"></a>';
    echo '<button class="menu-toggle" type="button" aria-expanded="false" aria-label="Menü öffnen">☰</button>';
    echo '<nav class="main-nav" aria-label="Hauptnavigation">';
    echo '<a'.$a('home').' href="/">Home</a>';
    echo '<a'.$a('angebote').' href="/angebote/">Angebote</a>';
    echo '<a'.$a('trainerteam').' href="/trainerteam/">Trainerteam</a>';
    echo '<div class="nav-dropdown"><a'.$a('standorte').' href="/standorte/">Standorte</a><div class="dropdown-menu"><a href="/tsv-feldkirchen/">TSV Feldkirchen</a><a href="/sv-heimstetten/">SV Heimstetten</a></div></div>';
    echo '<a href="https://shop.tennisschule-alexbraun.de/">Shop</a>';
    echo '<a'.$a('kontakt').' href="/kontakt/">Kontakt</a>';
    echo '</nav></div></header>';
}
function site_hero(bool $home = false): void {
    $d = site_data();
    $tagline = $d['site']['tagline'] ?? '';
    $copy = $d['site']['hero_copy'] ?? '';
    $heroImage = (string)($d['site']['hero_image'] ?? '/assets/media/header_bild.jpg');
    $heroImage = str_replace(["'", '"', ')', "\\"], '', $heroImage);
    $heroStyle = "--hero-image:url('" . $heroImage . "')";
    echo '<section class="hero '.($home ? 'home-hero' : 'page-hero').'" style="'.h($heroStyle).'"><div class="hero-overlay"></div><div class="container hero-content">';
    echo '<p class="eyebrow">Tennisschule Alex Braun</p><h1><span>Tennisschule</span>Alex Braun</h1>';
    if ($home) {
        echo '<p class="hero-lead">'.h($tagline).'</p><p class="hero-copy">'.h($copy).'</p>';
        echo '<div class="hero-actions"><a class="btn btn-primary" href="/angebote/">Anmeldung</a><a class="btn btn-outline-light" href="/kontakt/">Kontakt</a></div>';
    }
    echo '</div></section>';
}
function site_footer(): void {
    echo '<footer class="site-footer"><div class="container footer-inner">';
    echo '<a href="/">Tennisschule Alex Braun</a><a href="/impressum-datenschutzerklaerung/">Impressum &amp; Datenschutzerklärung</a><span>© '.date('Y').' Tennisschule Alex Braun</span>';
    echo '</div></footer><script src="/assets/site.js"></script>';
}
function page_head(string $title, string $description = ''): void {
    if ($description === '') $description = $title . ' - Tennisschule Alex Braun';
    echo '<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>'.h($title).' - Tennisschule Alex Braun</title><meta name="description" content="'.h($description).'"><link rel="stylesheet" href="/assets/style.css"></head><body>';
}
