<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function github_api(string $method, string $url, ?array $body = null): array {
    $cfg = app_config()['github'];
    $token = (string)($cfg['token'] ?? '');
    if ($token === '') throw new RuntimeException('GitHub Token ist nicht konfiguriert.');
    if (!function_exists('curl_init')) throw new RuntimeException('PHP-cURL ist auf dem Webspace nicht verfügbar.');

    $ch = curl_init($url);
    $headers = [
        'Accept: application/vnd.github+json',
        'Authorization: Bearer ' . $token,
        'X-GitHub-Api-Version: 2022-11-28',
        'User-Agent: Tennisschule-AlexBraun-CMS',
    ];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 25,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_SLASHES));
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $raw = curl_exec($ch);
    if ($raw === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('GitHub-Verbindung fehlgeschlagen: ' . $error);
    }
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $decoded = json_decode($raw, true);
    if ($status < 200 || $status >= 300) {
        $msg = is_array($decoded) ? ($decoded['message'] ?? 'GitHub API Fehler') : 'GitHub API Fehler';
        throw new RuntimeException($msg . ' (HTTP ' . $status . ')');
    }
    return is_array($decoded) ? $decoded : [];
}

function github_encoded_path(string $path): string {
    return implode('/', array_map('rawurlencode', explode('/', $path)));
}

function github_put_file(string $path, string $bytes, string $message): ?string {
    $cfg = app_config()['github'];
    $owner = rawurlencode((string)$cfg['owner']);
    $repo = rawurlencode((string)$cfg['repo']);
    $branch = (string)$cfg['branch'];
    $url = "https://api.github.com/repos/{$owner}/{$repo}/contents/" . github_encoded_path($path);

    $sha = null;
    try {
        $current = github_api('GET', $url . '?ref=' . rawurlencode($branch));
        $sha = $current['sha'] ?? null;
    } catch (RuntimeException $e) {
        if (!str_contains($e->getMessage(), '404')) {
            // A missing file is expected for new uploads; other failures will surface on PUT.
        }
    }

    $payload = [
        'message' => $message,
        'content' => base64_encode($bytes),
        'branch' => $branch,
    ];
    if ($sha) $payload['sha'] = $sha;

    $result = github_api('PUT', $url, $payload);
    return $result['commit']['sha'] ?? null;
}
