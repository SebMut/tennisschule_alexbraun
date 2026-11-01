<?php
declare(strict_types=1);

/**
 * Transaktionale Kontakt-E-Mails im Erscheinungsbild der Tennisschule.
 * Inline-CSS und Tabellenlayout für Mailprogramme ohne modernes CSS.
 * Externe Grafiken können blockiert sein: Alle Inhalte bleiben als Text lesbar.
 */
function contact_mail_escape(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function contact_mail_plain(string $title, string $intro, array $details, string $message, string $note): string {
    $lines = ["Tennisschule Alex Braun", "", $title, "", $intro, ""];
    foreach ($details as $label => $value) {
        $lines[] = (string)$label . ': ' . (string)$value;
    }
    $lines[] = '';
    $lines[] = 'Nachricht:';
    $lines[] = $message;
    $lines[] = '';
    $lines[] = $note;
    $lines[] = '';
    $lines[] = 'Tennisschule Alex Braun';
    $lines[] = 'info@tennisschule-alexbraun.de';
    $lines[] = 'https://www.tennisschule-alexbraun.de/';
    return implode("\n", $lines);
}

function contact_mail_html(
    string $kicker,
    string $title,
    string $intro,
    array $details,
    string $message,
    string $note
): string {
    $detailRows = '';
    foreach ($details as $label => $value) {
        $detailRows .= '<tr>'
            . '<td style="padding:11px 12px 11px 0;border-bottom:1px solid #E9EBF0;color:#687082;font-size:13px;font-weight:700;vertical-align:top;width:110px;">'
            . contact_mail_escape((string)$label)
            . '</td><td style="padding:11px 0;border-bottom:1px solid #E9EBF0;color:#2B2D42;font-size:14px;line-height:1.5;vertical-align:top;overflow-wrap:anywhere;">'
            . contact_mail_escape((string)$value)
            . '</td></tr>';
    }
    $messageHtml = nl2br(contact_mail_escape($message), false);
    $logo = 'https://www.tennisschule-alexbraun.de/assets/media/logo.png';
    $site = 'https://www.tennisschule-alexbraun.de/';
    return '<!doctype html><html lang="de"><head>'
        . '<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . contact_mail_escape($title) . '</title></head>'
        . '<body style="margin:0;padding:0;background-color:#F3F4F7;color:#2B2D42;font-family:Mulish,Arial,Helvetica,sans-serif;">'
        . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;background-color:#F3F4F7;">'
        . '<tr><td align="center" style="padding:32px 14px;">'
        . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="border-collapse:separate;border-spacing:0;width:100%;max-width:600px;background:#FFFFFF;border-radius:12px;overflow:hidden;">'
        . '<tr><td style="padding:22px 32px;background-color:#FFFFFF;">'
        . '<a href="' . $site . '" style="text-decoration:none;color:#2B2D42;" target="_blank">'
        . '<img src="' . $logo . '" alt="Tennisschule Alex Braun" width="205" style="display:block;width:205px;max-width:100%;height:auto;border:0;">'
        . '</a></td></tr>'
        . '<tr><td style="height:5px;line-height:5px;font-size:1px;background-color:#F17C20;">&nbsp;</td></tr>'
        . '<tr><td style="padding:30px 32px 32px;background-color:#2B2D42;">'
        . '<p style="margin:0 0 10px;color:#FFB46A;font-size:12px;line-height:1.5;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;">'
        . contact_mail_escape($kicker) . '</p>'
        . '<h1 style="margin:0;color:#FFFFFF;font-size:27px;line-height:1.25;font-weight:700;">'
        . contact_mail_escape($title) . '</h1></td></tr>'
        . '<tr><td style="padding:31px 32px 25px;background-color:#FFFFFF;">'
        . '<p style="margin:0 0 23px;color:#45495A;font-size:16px;line-height:1.65;">'
        . contact_mail_escape($intro) . '</p>'
        . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;width:100%;margin:0 0 25px;">'
        . $detailRows . '</table>'
        . '<p style="margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#2B2D42;">Deine Nachricht</p>'
        . '<div style="padding:18px 20px;background-color:#F6F7FA;border-left:4px solid #F17C20;border-radius:4px;color:#2B2D42;font-size:15px;line-height:1.65;overflow-wrap:anywhere;">'
        . $messageHtml . '</div>'
        . '<p style="margin:24px 0 0;color:#5E6371;font-size:14px;line-height:1.65;">'
        . contact_mail_escape($note) . '</p>'
        . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:25px;border-collapse:collapse;">'
        . '<tr><td style="background-color:#F17C20;border-radius:6px;">'
        . '<a href="' . $site . '" target="_blank" style="display:inline-block;padding:12px 22px;color:#FFFFFF;background-color:#F17C20;border-radius:6px;text-decoration:none;font-size:13px;font-weight:700;letter-spacing:.4px;">Zur Website</a>'
        . '</td></tr></table>'
        . '</td></tr>'
        . '<tr><td style="padding:22px 32px 27px;background-color:#F8F9FB;border-top:1px solid #EAECF1;">'
        . '<p style="margin:0 0 7px;color:#2B2D42;font-size:14px;font-weight:700;">Tennisschule Alex Braun</p>'
        . '<p style="margin:0;color:#687082;font-size:12px;line-height:1.8;">'
        . '<a href="mailto:info@tennisschule-alexbraun.de" style="color:#2F7CFF;text-decoration:none;">info@tennisschule-alexbraun.de</a>'
        . ' &nbsp;·&nbsp; <a href="' . $site . '" style="color:#2F7CFF;text-decoration:none;">tennisschule-alexbraun.de</a>'
        . '</p></td></tr></table>'
        . '<p style="margin:16px 0 0;color:#798091;font-size:12px;line-height:1.6;text-align:center;">Diese E-Mail gehört zu einer Anfrage über das Kontaktformular.</p>'
        . '</td></tr></table></body></html>';
}
