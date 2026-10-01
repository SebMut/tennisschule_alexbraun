<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function start_admin_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name('ts_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function csrf_token(): string {
    start_admin_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return (string)$_SESSION['csrf'];
}

function is_authenticated(): bool {
    start_admin_session();
    return !empty($_SESSION['authenticated']);
}

function require_auth(): void {
    if (!is_authenticated()) json_response(['ok'=>false,'error'=>'Nicht angemeldet.'], 401);
}

function require_csrf(): void {
    start_admin_session();
    $sent = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($sent === '' || empty($_SESSION['csrf']) || !hash_equals((string)$_SESSION['csrf'], $sent)) {
        json_response(['ok'=>false,'error'=>'Ungültige Sitzung. Bitte neu anmelden.'], 403);
    }
}
