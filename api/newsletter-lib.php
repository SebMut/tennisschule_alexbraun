<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function newsletter_file(): string {
    $dir = storage_dir() . '/newsletter';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return $dir . '/subscribers.json';
}
function newsletter_load(): array {
    $file = newsletter_file();
    if (!is_file($file)) return [];
    $data = json_decode((string)file_get_contents($file), true);
    return is_array($data) ? $data : [];
}
function newsletter_save(array $items): void {
    $file = newsletter_file();
    $tmp = $file . '.tmp';
    file_put_contents($tmp, json_encode(array_values($items), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), LOCK_EX);
    rename($tmp, $file);
}
function newsletter_site_data(): array {
    $file = site_json_path();
    $data = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    return is_array($data) ? $data : [];
}
function newsletter_base_url(): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $host = preg_replace('/[^A-Za-z0-9.:-]/', '', (string)($_SERVER['HTTP_HOST'] ?? 'test.tennisschule-alexbraun.de'));
    return ($https ? 'https://' : 'http://') . $host;
}
function newsletter_normalize_email(string $email): string {
    return strtolower(trim($email));
}
function newsletter_find_index(array $items, string $email): int {
    $email = newsletter_normalize_email($email);
    foreach ($items as $i => $item) {
        if (newsletter_normalize_email((string)($item['email'] ?? '')) === $email) return (int)$i;
    }
    return -1;
}
function newsletter_token(): string {
    return bin2hex(random_bytes(32));
}
function newsletter_hash(string $token): string {
    return hash('sha256', $token);
}
function newsletter_rate_limit(string $email): void {
    $dir = storage_dir() . '/newsletter-rate';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $key = hash('sha256', newsletter_normalize_email($email) . '|' . client_ip());
    $file = $dir . '/' . $key . '.json';
    $state = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    if (!is_array($state)) $state = [];
    $now = time();
    $attempts = array_values(array_filter($state['attempts'] ?? [], fn($t) => (int)$t > $now - 3600));
    if (count($attempts) >= 5) json_response(['ok'=>false,'error'=>'Bitte später erneut versuchen.'], 429);
    $attempts[] = $now;
    file_put_contents($file, json_encode(['attempts'=>$attempts]), LOCK_EX);
}
