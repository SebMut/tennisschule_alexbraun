<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/smtp.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /kontakt/?status=error', true, 303);
    exit;
}

if (trim((string)($_POST['website'] ?? '')) !== '') {
    header('Location: /nachricht-erfolgreich-zugestellt/', true, 303);
    exit;
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

$rateFile = storage_dir() . '/contact-' . hash('sha256', client_ip()) . '.txt';
$last = is_file($rateFile) ? (int)trim((string)file_get_contents($rateFile)) : 0;
if ($last > 0 && (time() - $last) < 30) {
    header('Location: /kontakt/?status=error', true, 303);
    exit;
}
@file_put_contents($rateFile, (string)time(), LOCK_EX);

$clean = static fn(string $v): string => str_replace(["\r","\n"], ' ', $v);
$mailBody =
    "Neue Kontaktanfrage über tennisschule-alexbraun.de\n\n" .
    "Name: " . $clean($name) . "\n" .
    "E-Mail: " . $clean($email) . "\n" .
    "Telefon: " . ($phone !== '' ? $clean($phone) : 'nicht angegeben') . "\n" .
    "Anliegen: " . $clean($topic) . "\n\n" .
    "Nachricht:\n" . $message . "\n";

try {
    smtp_send([
        'subject' => 'Website: ' . $topic,
        'reply_to' => $email,
        'body' => $mailBody,
    ]);
    header('Location: /nachricht-erfolgreich-zugestellt/', true, 303);
} catch (Throwable $e) {
    error_log('Kontaktformular SMTP: ' . $e->getMessage());
    header('Location: /kontakt/?status=error', true, 303);
}
exit;
