<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/smtp.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /kontakt/?status=error', true, 303);
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$submittedToken = (string)($_POST['contact_token'] ?? '');
$sessionToken = (string)($_SESSION['contact_token'] ?? '');
$loadedAt = (int)($_SESSION['contact_form_loaded_at'] ?? 0);
if ($sessionToken === '' || !hash_equals($sessionToken, $submittedToken)
    || $loadedAt === 0 || time() - $loadedAt < 3 || time() - $loadedAt > 7200) {
    header('Location: /kontakt/?status=error', true, 303);
    exit;
}
if (trim((string)($_POST['website'] ?? '')) !== '') {
    header('Location: /nachricht-erfolgreich-zugestellt/', true, 303);
    exit;
}

// Turnstile wird erst aktiviert, wenn beide Schlüssel sicher konfiguriert sind.
$turnstile = app_config()['turnstile'] ?? [];
$turnstileSiteKey = (string)($turnstile['site_key'] ?? '');
$turnstileSecret = (string)($turnstile['secret_key'] ?? '');
if ($turnstileSiteKey !== '' || $turnstileSecret !== '') {
    if ($turnstileSiteKey === '' || $turnstileSecret === '') {
        error_log('Kontaktformular: Turnstile-Konfiguration unvollständig');
        header('Location: /kontakt/?status=error', true, 303);
        exit;
    }
    $responseToken = (string)($_POST['cf-turnstile-response'] ?? '');
    if ($responseToken === '' || strlen($responseToken) > 2048) {
        header('Location: /kontakt/?status=error', true, 303);
        exit;
    }
    $verifyBody = http_build_query([
        'secret' => $turnstileSecret,
        'response' => $responseToken,
        'remoteip' => client_ip(),
    ]);
    $context = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $verifyBody,
        'timeout' => 6,
        'ignore_errors' => true,
    ]]);
    $verifyJson = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $context);
    $verify = is_string($verifyJson) ? json_decode($verifyJson, true) : null;
    $host = strtolower((string)($verify['hostname'] ?? ''));
    if (!is_array($verify) || ($verify['success'] ?? false) !== true
        || !in_array($host, ['tennisschule-alexbraun.de', 'www.tennisschule-alexbraun.de'], true)) {
        header('Location: /kontakt/?status=error', true, 303);
        exit;
    }
}

$name = trim((string)($_POST['name'] ?? ''));
$topic = trim((string)($_POST['topic'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

$allowedTopics = ['Sommertraining','Wintertraining','Tenniscamp','Einzeltraining','Probetraining','Sonstiges'];
$valid = $name !== '' && $topic !== '' && in_array($topic, $allowedTopics, true) && $message !== ''
    && filter_var($email, FILTER_VALIDATE_EMAIL)
    && strlen($name) <= 120 && strlen($topic) <= 80
    && strlen($phone) <= 60 && strlen($email) <= 190 && strlen($message) <= 5000;

if (!$valid) {
    header('Location: /kontakt/?status=error', true, 303);
    exit;
}

// Zusätzlich zur 30-Sekunden-Sperre maximal fünf Versuche pro Stunde und IP.
$hourFile = storage_dir() . '/contact-hour-' . hash('sha256', client_ip()) . '.json';
$now = time();
$hourState = is_file($hourFile) ? json_decode((string)@file_get_contents($hourFile), true) : null;
if (!is_array($hourState) || ($now - (int)($hourState['start'] ?? 0)) >= 3600) {
    $hourState = ['start' => $now, 'count' => 0];
}
if ((int)$hourState['count'] >= 5) {
    header('Location: /kontakt/?status=error', true, 303);
    exit;
}
$hourState['count']++;
@file_put_contents($hourFile, json_encode($hourState), LOCK_EX);

$rateFile = storage_dir() . '/contact-' . hash('sha256', client_ip()) . '.txt';
$last = is_file($rateFile) ? (int)trim((string)file_get_contents($rateFile)) : 0;
if ($last > 0 && (time() - $last) < 30) {
    header('Location: /kontakt/?status=error', true, 303);
    exit;
}
@file_put_contents($rateFile, (string)time(), LOCK_EX);

// Ein gültiges Formular darf nur einmal verwendet werden.
unset($_SESSION['contact_token'], $_SESSION['contact_form_loaded_at']);
session_write_close();

$clean = static fn(string $v): string => trim(str_replace(["\r","\n"], ' ', $v));
$senderName = $clean($name);
$senderPhone = $clean($phone);
$ownerEmail = (string)(app_config()['smtp']['to'] ?? 'info@tennisschule-alexbraun.de');
$adminFields = [
    'Name' => $senderName,
    'E-Mail' => $email,
    'Telefon' => $senderPhone !== '' ? $senderPhone : 'nicht angegeben',
    'Anliegen' => $topic,
];

require __DIR__ . '/contact-mail-templates.php';

// Zuerst zuverlässig an die Tennisschule senden. Nur danach erfolgt eine Bestätigung.
try {
    smtp_send([
        'to' => $ownerEmail,
        'subject' => 'Neue Kontaktanfrage: ' . $topic,
        'reply_to' => $email,
        'body' => contact_mail_plain(
            'Neue Kontaktanfrage',
            'Über die Website ist eine neue Kontaktanfrage eingegangen.',
            $adminFields,
            $message,
            'Du kannst direkt auf diese E-Mail antworten, um mit der anfragenden Person Kontakt aufzunehmen.'
        ),
        'html' => contact_mail_html(
            'Kontaktformular · neue Anfrage',
            'Neue Kontaktanfrage',
            'Über die Website ist eine neue Kontaktanfrage eingegangen.',
            $adminFields,
            $message,
            'Du kannst direkt auf diese E-Mail antworten, um mit der anfragenden Person Kontakt aufzunehmen.'
        ),
    ]);
} catch (Throwable $e) {
    error_log('Kontaktformular SMTP (Tennisschule): ' . $e->getMessage());
    header('Location: /kontakt/?status=error', true, 303);
    exit;
}

// Automatische Eingangsbestätigung: nicht mit der Anfrage verwechseln.
// Ein Problem mit der Bestätigung darf keine zweite Anfrage auslösen.
try {
    $confirmationFields = ['Anliegen' => $topic];
    $intro = 'Hallo ' . $senderName . ', vielen Dank für deine Nachricht! Wir haben deine Anfrage erhalten und melden uns so bald wie möglich bei dir.';
    $note = 'Dies ist eine automatische Eingangsbestätigung. Wenn du noch Fragen hast, kannst du einfach auf diese E-Mail antworten.';
    smtp_send([
        'to' => $email,
        'subject' => 'Deine Nachricht ist angekommen | Tennisschule Alex Braun',
        'reply_to' => $ownerEmail,
        'auto_response' => true,
        'body' => contact_mail_plain(
            'Vielen Dank für deine Nachricht!',
            $intro,
            $confirmationFields,
            $message,
            $note
        ),
        'html' => contact_mail_html(
            'Eingangsbestätigung',
            'Vielen Dank für deine Nachricht!',
            $intro,
            $confirmationFields,
            $message,
            $note
        ),
    ]);
} catch (Throwable $e) {
    // Die Anfrage an die Tennisschule wurde bereits erfolgreich verschickt.
    error_log('Kontaktformular SMTP (Eingangsbestätigung): ' . $e->getMessage());
}
header('Location: /nachricht-erfolgreich-zugestellt/', true, 303);
exit;
