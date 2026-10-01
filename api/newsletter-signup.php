<?php
declare(strict_types=1);
require __DIR__ . '/newsletter-lib.php';
require __DIR__ . '/smtp.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok'=>false],405);

$name = trim((string)($_POST['name'] ?? ''));
$email = newsletter_normalize_email((string)($_POST['email'] ?? ''));
$consent = (string)($_POST['consent'] ?? '');
$website = trim((string)($_POST['website'] ?? ''));

if ($website !== '') json_response(['ok'=>true]);
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $consent !== 'yes' || strlen($name) > 120) {
    json_response(['ok'=>false,'error'=>'Bitte E-Mail-Adresse und Einwilligung prüfen.'],422);
}
newsletter_rate_limit($email);

$site = newsletter_site_data();
$cfg = $site['newsletter'] ?? [];
if (empty($cfg['enabled'])) json_response(['ok'=>false,'error'=>'Die Newsletter-Anmeldung ist derzeit deaktiviert.'],403);

$items = newsletter_load();
$idx = newsletter_find_index($items,$email);
$now = date(DATE_ATOM);

if ($idx >= 0 && ($items[$idx]['status'] ?? '') === 'active') {
    json_response(['ok'=>true,'status'=>'active','message'=>$cfg['confirmed_message'] ?? 'Bereits angemeldet.']);
}

$confirmToken = newsletter_token();
$unsubscribeToken = newsletter_token();
$entry = [
    'email'=>$email,
    'name'=>$name,
    'status'=>'pending',
    'created_at'=>$idx >= 0 ? ($items[$idx]['created_at'] ?? $now) : $now,
    'updated_at'=>$now,
    'confirmed_at'=>null,
    'unsubscribed_at'=>null,
    'confirm_hash'=>newsletter_hash($confirmToken),
    'unsubscribe_hash'=>newsletter_hash($unsubscribeToken),
    'consent_at'=>$now,
    'consent_text'=>$cfg['consent_label'] ?? '',
];
if ($idx >= 0) $items[$idx] = array_merge($items[$idx],$entry);
else $items[] = $entry;
newsletter_save($items);

$base = newsletter_base_url();
$confirmUrl = $base . '/newsletter/bestaetigen/?email=' . rawurlencode($email) . '&token=' . rawurlencode($confirmToken);
$body = ($cfg['confirmation_intro'] ?? 'Bitte bestätige deine Anmeldung:') . "\n\n" . $confirmUrl . "\n\n" .
        "Falls du dich nicht angemeldet hast, kannst du diese E-Mail ignorieren.";

try {
    smtp_send(['to'=>$email,'subject'=>$cfg['confirmation_subject'] ?? 'Newsletter-Anmeldung bestätigen','body'=>$body]);
} catch (Throwable $e) {
    json_response(['ok'=>false,'error'=>$cfg['error_message'] ?? 'E-Mail konnte nicht versendet werden.'],500);
}

json_response(['ok'=>true,'status'=>'pending','message'=>$cfg['pending_message'] ?? 'Bitte Anmeldung per E-Mail bestätigen.']);
