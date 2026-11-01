<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function smtp_read($fp): string {
    $response = '';
    while (($line = fgets($fp, 515)) !== false) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') break;
    }
    return $response;
}

function smtp_expect($fp, array $codes): string {
    $response = smtp_read($fp);
    $code = (int)substr($response, 0, 3);
    if (!in_array($code, $codes, true)) {
        throw new RuntimeException('SMTP-Fehler: ' . trim($response));
    }
    return $response;
}

function smtp_command($fp, string $command, array $codes): string {
    fwrite($fp, $command . "\r\n");
    return smtp_expect($fp, $codes);
}

function smtp_header_value(string $value): string {
    $value = str_replace(["\r","\n"], ' ', trim($value));
    if (function_exists('mb_encode_mimeheader')) {
        return mb_encode_mimeheader($value, 'UTF-8', 'B', "\r\n");
    }
    return $value;
}

function smtp_send(array $mail): void {
    $cfg = app_config()['smtp'];
    $host = (string)$cfg['host'];
    $port = (int)$cfg['port'];
    $encryption = strtolower((string)$cfg['encryption']);
    $user = (string)$cfg['username'];
    $pass = (string)$cfg['password'];
    $from = (string)$cfg['from'];
    $fromName = (string)$cfg['from_name'];
    $to = trim((string)($mail['to'] ?? $cfg['to']));

    if ($host === '' || $user === '' || $pass === '' || $from === '' || $to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('SMTP ist noch nicht vollständig konfiguriert.');
    }

    $transport = $encryption === 'ssl' ? 'ssl://' : 'tcp://';
    $fp = @stream_socket_client($transport . $host . ':' . $port, $errno, $errstr, 20, STREAM_CLIENT_CONNECT);
    if (!$fp) throw new RuntimeException('SMTP-Verbindung fehlgeschlagen: ' . $errstr);
    stream_set_timeout($fp, 20);

    try {
        smtp_expect($fp, [220]);
        $ehlo = preg_replace('/[^A-Za-z0-9.-]/', '', (string)($_SERVER['SERVER_NAME'] ?? 'localhost')) ?: 'localhost';
        smtp_command($fp, 'EHLO ' . $ehlo, [250]);

        if ($encryption === 'tls') {
            smtp_command($fp, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('TLS-Verschlüsselung konnte nicht aktiviert werden.');
            }
            smtp_command($fp, 'EHLO ' . $ehlo, [250]);
        }

        smtp_command($fp, 'AUTH LOGIN', [334]);
        smtp_command($fp, base64_encode($user), [334]);
        smtp_command($fp, base64_encode($pass), [235]);

        smtp_command($fp, 'MAIL FROM:<' . $from . '>', [250]);
        smtp_command($fp, 'RCPT TO:<' . $to . '>', [250,251]);
        smtp_command($fp, 'DATA', [354]);

        $replyTo = trim(str_replace(["\r","\n"], '', (string)($mail['reply_to'] ?? '')));
        if ($replyTo !== '' && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Ungültige Antwortadresse.');
        }
        $subject = smtp_header_value((string)($mail['subject'] ?? 'Kontaktanfrage'));
        $body = str_replace(["\r\n","\r"], "\n", (string)($mail['body'] ?? ''));
        $body = str_replace("\n", "\r\n", $body);
        $html = (string)($mail['html'] ?? '');

        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . smtp_header_value($fromName) . ' <' . $from . '>',
            'To: <' . $to . '>',
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
        ];
        if ($replyTo !== '') $headers[] = 'Reply-To: <' . $replyTo . '>';
        if (!empty($mail['auto_response'])) {
            $headers[] = 'Auto-Submitted: auto-replied';
            $headers[] = 'X-Auto-Response-Suppress: All';
        }

        if ($html !== '') {
            // Multipart: lesbare Textversion und gestaltete HTML-Version.
            // Base64 hält UTF-8 und lange HTML-Zeilen SMTP-sicher.
            $boundary = '=_tsab_' . bin2hex(random_bytes(12));
            $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
            $body = '--' . $boundary . "\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode($body), 76, "\r\n")
                . "\r\n--" . $boundary . "\r\n"
                . "Content-Type: text/html; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode($html), 76, "\r\n")
                . "\r\n--" . $boundary . '--';
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $headers[] = 'Content-Transfer-Encoding: 8bit';
        }
        $body = preg_replace('/^\./m', '..', $body);
        fwrite($fp, implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n");
        smtp_expect($fp, [250]);
        smtp_command($fp, 'QUIT', [221]);
    } finally {
        fclose($fp);
    }
}
