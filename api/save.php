<?php
declare(strict_types=1);
require __DIR__ . '/auth-lib.php';
require __DIR__ . '/github.php';
require_auth();
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok'=>false,'error'=>'Methode nicht erlaubt.'], 405);
$body = read_json_body();
$data = $body['data'] ?? null;
if (!is_array($data)) json_response(['ok'=>false,'error'=>'Ungültige Daten.'], 422);

foreach (['site','home','offers','trainers','locations'] as $required) {
    if (!array_key_exists($required, $data)) json_response(['ok'=>false,'error'=>'Unvollständige Inhaltsdaten.'], 422);
}

// Preserve newly introduced fields even if an older CMS tab is saved later.
$current = [];
$currentPath = site_json_path();
if (is_file($currentPath)) {
    $decoded = json_decode((string)file_get_contents($currentPath), true);
    if (is_array($decoded)) $current = $decoded;
}

$currentOffersById = [];
foreach (($current['offers']['items'] ?? []) as $currentOffer) {
    if (!empty($currentOffer['id'])) $currentOffersById[(string)$currentOffer['id']] = $currentOffer;
}
foreach (($data['offers']['items'] ?? []) as &$offer) {
    $id = (string)($offer['id'] ?? '');
    if (!array_key_exists('registration_open', $offer)) {
        if (isset($currentOffersById[$id]) && array_key_exists('registration_open', $currentOffersById[$id])) {
            $offer['registration_open'] = (bool)$currentOffersById[$id]['registration_open'];
        } else {
            $offer['registration_open'] = (($offer['status_kind'] ?? '') === 'open');
        }
    } else {
        $offer['registration_open'] = (bool)$offer['registration_open'];
    }
    if (empty($offer['registration_start'])) {
        $offer['registration_start'] = (string)($currentOffersById[$id]['registration_start']
            ?? $offer['booking_date']
            ?? $currentOffersById[$id]['booking_date']
            ?? '');
    }
    if (!array_key_exists('registration_end', $offer)) {
        $offer['registration_end'] = (string)($currentOffersById[$id]['registration_end'] ?? '');
    }
    if (empty($offer['booking_date']) && !empty($currentOffersById[$id]['booking_date'])) {
        $offer['booking_date'] = (string)$currentOffersById[$id]['booking_date'];
    }
    $offer['featured'] = !empty($offer['featured']);
}
unset($offer);

// Preserve trainer visibility if an older CMS tab does not know the field yet.
$currentTrainers = $current['trainers']['items'] ?? [];
foreach (($data['trainers']['items'] ?? []) as $i => &$trainer) {
    if (!array_key_exists('visible', $trainer)) {
        $trainer['visible'] = array_key_exists('visible', $currentTrainers[$i] ?? [])
            ? (bool)$currentTrainers[$i]['visible']
            : true;
    } else {
        $trainer['visible'] = (bool)$trainer['visible'];
    }
}
unset($trainer);

$data['locations']['map_route_button'] = (string)($data['locations']['map_route_button']
    ?? $current['locations']['map_route_button']
    ?? 'Route mit Mapbox berechnen');
$data['locations']['map_route_loading'] = (string)($data['locations']['map_route_loading']
    ?? $current['locations']['map_route_loading']
    ?? 'Standort wird ermittelt …');
$data['locations']['map_route_error'] = (string)($data['locations']['map_route_error']
    ?? $current['locations']['map_route_error']
    ?? 'Dein Standort konnte nicht ermittelt werden. Bitte erlaube den Standortzugriff und versuche es erneut.');

$mapDefaults = [
    'feldkirchen' => ['latitude'=>48.143427223206544,'longitude'=>11.719520683624244],
    'heimstetten' => ['latitude'=>48.16306908434989,'longitude'=>11.74709917555206],
];
foreach ($mapDefaults as $key => $coords) {
    $currentLocation = $current['locations'][$key] ?? [];
    $data['locations'][$key]['latitude'] = (float)($data['locations'][$key]['latitude']
        ?? $currentLocation['latitude']
        ?? $coords['latitude']);
    $data['locations'][$key]['longitude'] = (float)($data['locations'][$key]['longitude']
        ?? $currentLocation['longitude']
        ?? $coords['longitude']);
    unset($data['locations'][$key]['map_query'], $data['locations'][$key]['marker_link']);
}
unset($data['locations']['map_consent_text'], $data['locations']['map_consent_button']);

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($json === false || strlen($json) > 500000) json_response(['ok'=>false,'error'=>'Inhaltsdaten sind zu groß oder ungültig.'], 422);
$json .= "\n";

$path = site_json_path();
$tmp = $path . '.tmp-' . bin2hex(random_bytes(4));
if (@file_put_contents($tmp, $json, LOCK_EX) === false || !@rename($tmp, $path)) {
    @unlink($tmp);
    json_response(['ok'=>false,'error'=>'Änderungen konnten auf dem Webspace nicht gespeichert werden.'], 500);
}

$github = ['ok'=>false,'commit'=>null,'warning'=>null];
try {
    $github['commit'] = github_put_file('data/site.json', $json, 'Update website content via admin CMS');
    $github['ok'] = true;
} catch (Throwable $e) {
    $github['warning'] = $e->getMessage();
}

json_response(['ok'=>true,'saved_local'=>true,'github'=>$github]);
