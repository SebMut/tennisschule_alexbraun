<?php
declare(strict_types=1);
require dirname(__DIR__) . '/api/contact-mail-templates.php';

function check_contact_mail(bool $condition, string $description): void {
    if (!$condition) {
        fwrite(STDERR, "Kontakt-Mail-Test fehlgeschlagen: " . $description . "\n");
        exit(1);
    }
}

$message = "Hallo!\n<script>alert('x')</script> & Grüße";
$fields = [
    'Name' => 'Max <Muster> & Co.',
    'Anliegen' => 'Probetraining',
];
$html = contact_mail_html(
    'Eingangsbestätigung',
    'Vielen Dank für deine Nachricht!',
    'Wir haben deine Nachricht erhalten.',
    $fields,
    $message,
    'Du kannst auf diese E-Mail antworten.'
);
$plain = contact_mail_plain(
    'Vielen Dank für deine Nachricht!',
    'Wir haben deine Nachricht erhalten.',
    $fields,
    $message,
    'Du kannst auf diese E-Mail antworten.'
);

check_contact_mail(str_contains($html, 'https://www.tennisschule-alexbraun.de/assets/media/logo.png'), 'Original-Logo eingebunden');
check_contact_mail(str_contains($html, '#F17C20') && str_contains($html, '#2B2D42'), 'Website-CI verwendet');
check_contact_mail(str_contains($html, '<html lang="de">'), 'gültiges HTML-Grundgerüst');
check_contact_mail(str_contains($html, 'Max &lt;Muster&gt; &amp; Co.'), 'Name HTML-escaped');
check_contact_mail(str_contains($html, '&lt;script&gt;alert(') && !str_contains($html, '<script>'), 'Nachricht HTML-escaped');
check_contact_mail(str_contains($html, '&amp; Grüße'), 'Umlaute korrekt verarbeitet');
check_contact_mail(str_contains($html, "Hallo!<br>\n"), 'Zeilenumbrüche erhalten');
check_contact_mail(str_contains($plain, "Hallo!\n<script>"), 'Textalternative vollständig');
check_contact_mail(str_contains($plain, 'Anliegen: Probetraining'), 'Textalternative enthält Anliegen');
echo "Kontakt-E-Mail-Vorlagen: Tests erfolgreich.\n";
