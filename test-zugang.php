<?php
declare(strict_types=1);
require __DIR__ . '/staging-access.php';

header('Cache-Control: no-store, private');
header('X-Robots-Tag: noindex, nofollow, noarchive', true);

if (!staging_access_is_test_host()) {
    header('Location: /', true, 302);
    exit;
}

staging_access_start_session();
$next = staging_access_safe_next((string)($_REQUEST['next'] ?? '/'));
$error = '';

if (staging_access_authenticated()) {
    header('Location: ' . $next, true, 302);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hash = staging_access_password_hash();
    $password = (string)($_POST['password'] ?? '');

    $storage = __DIR__ . '/storage';
    if (!is_dir($storage)) @mkdir($storage, 0755, true);
    $key = hash('sha256', substr((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 64));
    $rateFile = $storage . '/staging-login-' . $key . '.json';
    $rate = ['start'=>time(),'count'=>0];
    if (is_file($rateFile)) {
        $old = json_decode((string)file_get_contents($rateFile), true);
        if (is_array($old)) $rate = array_merge($rate, $old);
    }
    if ((time() - (int)$rate['start']) > 900) $rate = ['start'=>time(),'count'=>0];

    if ((int)$rate['count'] >= 8) {
        $error = 'Zu viele Anmeldeversuche. Bitte später erneut versuchen.';
    } elseif ($hash === '') {
        $error = 'Der Zugang ist noch nicht konfiguriert.';
    } elseif (!password_verify($password, $hash)) {
        $rate['count'] = (int)$rate['count'] + 1;
        @file_put_contents($rateFile, json_encode($rate), LOCK_EX);
        $error = 'Passwort nicht korrekt.';
    } else {
        @unlink($rateFile);
        session_regenerate_id(true);
        staging_access_mark_authenticated();
        header('Location: ' . $next, true, 302);
        exit;
    }
}
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow,noarchive">
<title>Testseite – Zugang</title>
<style>
*{box-sizing:border-box}html,body{min-height:100%}body{margin:0;display:grid;place-items:center;padding:24px;background:#f3f6fb;color:#2b2d42;font-family:Arial,sans-serif}.card{width:min(430px,100%);background:#fff;border:1px solid #e1e6ee;border-radius:16px;padding:30px;box-shadow:0 18px 50px rgba(31,53,91,.12)}.badge{display:inline-flex;padding:6px 9px;border-radius:999px;background:#fff1e7;color:#a84d08;font-size:12px;font-weight:700;margin-bottom:14px}h1{font-size:25px;margin:0 0 10px}p{color:#667085;line-height:1.5;margin:0 0 22px}label{display:block;font-size:13px;font-weight:700;margin-bottom:7px}input{width:100%;border:1px solid #cfd7e3;border-radius:9px;padding:12px;font-size:16px;outline:none}input:focus{border-color:#2f7cff;box-shadow:0 0 0 3px rgba(47,124,255,.12)}button{width:100%;margin-top:14px;border:0;border-radius:9px;padding:12px 14px;background:#2f7cff;color:#fff;font-weight:700;font-size:15px;cursor:pointer}.error{margin-top:14px;padding:10px 12px;border-radius:8px;background:#fff0f1;color:#a02020;font-size:13px}.note{font-size:12px;color:#8a91a0;margin-top:16px}
</style>
</head>
<body>
<main class="card">
  <span class="badge">Geschützte Testumgebung</span>
  <h1>Tennisschule Alex Braun</h1>
  <p>Diese Seite befindet sich noch im Aufbau. Bitte gib das Admin-Passwort ein, um die Testversion aufzurufen.</p>
  <form method="post" autocomplete="off">
    <input type="hidden" name="next" value="<?=htmlspecialchars($next, ENT_QUOTES, 'UTF-8')?>">
    <label for="password">Passwort</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required autofocus>
    <button type="submit">Testseite öffnen</button>
  </form>
  <?php if ($error !== ''): ?><div class="error"><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></div><?php endif; ?>
  <div class="note">Es gilt dasselbe Passwort wie für den Adminbereich.</div>
</main>
</body>
</html>
