<?php
declare(strict_types=1);

function staging_access_is_test_host(): bool {
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    $host = preg_replace('/:\\d+$/', '', $host);
    return $host === 'test.tennisschule-alexbraun.de';
}

function staging_access_config(): array {
    $path = __DIR__ . '/config.local.php';
    if (!is_file($path)) return [];
    $config = require $path;
    return is_array($config) ? $config : [];
}

function staging_access_start_session(): void {
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

function staging_access_password_hash(): string {
    $config = staging_access_config();
    return (string)($config['admin_password_hash'] ?? '');
}

function staging_access_signature(string $hash): string {
    return $hash === '' ? '' : hash('sha256', 'ts-staging-access|' . $hash);
}

function staging_access_authenticated(): bool {
    if (!staging_access_is_test_host()) return true;
    staging_access_start_session();
    $hash = staging_access_password_hash();
    if ($hash === '') return false;
    $expected = staging_access_signature($hash);
    return !empty($_SESSION['authenticated'])
        && !empty($_SESSION['staging_access_signature'])
        && hash_equals($expected, (string)$_SESSION['staging_access_signature']);
}

function staging_access_mark_authenticated(): void {
    staging_access_start_session();
    $hash = staging_access_password_hash();
    $_SESSION['authenticated'] = true;
    $_SESSION['staging_access_signature'] = staging_access_signature($hash);
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
}

function staging_access_safe_next(string $next): string {
    $next = trim($next);
    if ($next === '' || $next[0] !== '/' || str_starts_with($next, '//')) return '/';
    if (preg_match('/[\\r\\n]/', $next)) return '/';
    return $next;
}

function staging_access_require(bool $json = false): void {
    if (!staging_access_is_test_host() || staging_access_authenticated()) return;

    header('Cache-Control: no-store, private');
    header('X-Robots-Tag: noindex, nofollow, noarchive', true);

    if ($json) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'error' => 'Testseite ist passwortgeschützt. Bitte zuerst anmelden.',
            'login' => '/test-zugang.php',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $next = staging_access_safe_next($uri);
    header('Location: /test-zugang.php?next=' . rawurlencode($next), true, 302);
    exit;
}
