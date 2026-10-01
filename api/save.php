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
