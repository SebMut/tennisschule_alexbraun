<?php
declare(strict_types=1);
require __DIR__ . '/auth-lib.php';
require_auth();
require_csrf();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'] ?? '', (bool)$p['secure'], (bool)$p['httponly']);
}
session_destroy();
json_response(['ok'=>true]);
