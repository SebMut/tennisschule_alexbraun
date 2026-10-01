<?php
declare(strict_types=1);

function app_config(): array {
    static $config = null;
    if ($config !== null) return $config;

    $defaults = [
        'admin_password_hash' => getenv('TS_ADMIN_PASSWORD_HASH') ?: '',
        'github' => [
            'token' => getenv('TS_GITHUB_TOKEN') ?: '',
            'owner' => getenv('TS_GITHUB_OWNER') ?: 'SebMut',
            'repo' => getenv('TS_GITHUB_REPO') ?: 'tennisschule_alexbraun',
            'branch' => getenv('TS_GITHUB_BRANCH') ?: 'main',
        ],
        'smtp' => [
            'host' => getenv('TS_SMTP_HOST') ?: 'smtps.udag.de',
            'port' => (int)(getenv('TS_SMTP_PORT') ?: 587),
            'encryption' => getenv('TS_SMTP_ENCRYPTION') ?: 'tls',
            'username' => getenv('TS_SMTP_USER') ?: '',
            'password' => getenv('TS_SMTP_PASS') ?: '',
            'from' => getenv('TS_SMTP_FROM') ?: 'info@tennisschule-alexbraun.de',
            'from_name' => getenv('TS_SMTP_FROM_NAME') ?: 'Tennisschule Alex Braun',
            'to' => getenv('TS_SMTP_TO') ?: 'info@tennisschule-alexbraun.de',
        ],
    ];

    $local = dirname(__DIR__) . '/config.local.php';
    if (is_file($local)) {
        $user = require $local;
        if (is_array($user)) $defaults = array_replace_recursive($defaults, $user);
    }

    $config = $defaults;
    return $config;
}

function json_response(array $payload, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function read_json_body(): array {
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function site_json_path(): string {
    return dirname(__DIR__) . '/data/site.json';
}

function storage_dir(): string {
    $dir = dirname(__DIR__) . '/storage';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return $dir;
}

function client_ip(): string {
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 64);
}
