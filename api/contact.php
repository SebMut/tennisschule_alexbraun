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
