<?php
declare(strict_types=1);
require __DIR__ . '/auth-lib.php';

start_admin_session();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_response([
        'ok' => true,
        'authenticated' => is_authenticated(),
        'csrf' => is_authenticated() ? csrf_token() : null,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok'=>false,'error'=>'Methode nicht erlaubt.'], 405);

$config = app_config();
$hash = (string)($config['admin_password_hash'] ?? '');
if ($hash === '') json_response(['ok'=>false,'error'=>'Admin-Passwort ist noch nicht konfiguriert.'], 503);

$key = hash('sha256', client_ip());
$rateFile = storage_dir() . '/login-v2-' . $key . '.json';
$rate = ['start'=>time(),'count'=>0];
if (is_file($rateFile)) {
    $old = json_decode((string)file_get_contents($rateFile), true);
    if (is_array($old)) $rate = array_merge($rate, $old);
}
if ((time() - (int)$rate['start']) > 900) $rate = ['start'=>time(),'count'=>0];
if ((int)$rate['count'] >= 8) json_response(['ok'=>false,'error'=>'Zu viele Anmeldeversuche. Bitte später erneut versuchen.'], 429);

$body = read_json_body();
$password = (string)($body['password'] ?? '');
if (!password_verify($password, $hash)) {
    $rate['count'] = (int)$rate['count'] + 1;
    @file_put_contents($rateFile, json_encode($rate), LOCK_EX);
    json_response(['ok'=>false,'error'=>'Passwort nicht korrekt.'], 401);
}

@unlink($rateFile);
session_regenerate_id(true);
$_SESSION['authenticated'] = true;
$_SESSION['csrf'] = bin2hex(random_bytes(24));
if (staging_access_is_test_host()) staging_access_mark_authenticated();
json_response(['ok'=>true,'authenticated'=>true,'csrf'=>$_SESSION['csrf']]);
