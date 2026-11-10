<?php
declare(strict_types=1);

/**
 * Popup-Zeitfenster in Europe/Berlin. Die Grenzen enthalten Minuten;
 * die Endminute zählt vollständig (bis zur letzten Millisekunde).
 * Zwei leere Felder erhalten das bisherige Verhalten: dauerhaft aktiv,
 * solange der manuelle Hauptschalter eingeschaltet ist.
 */
function popup_parse_datetime(string $raw): ?DateTimeImmutable {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/D', $raw)) return null;
    $tz = new DateTimeZone('Europe/Berlin');
    $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $raw, $tz);
    if (!$date || $date->format('Y-m-d\TH:i') !== $raw) return null;
    return $date;
}

function popup_schedule_state(array $home, ?DateTimeImmutable $now=null): string {
    if (empty($home['popup_enabled'])) return 'off';
    $startRaw = trim((string)($home['popup_start_at'] ?? ''));
    $endRaw = trim((string)($home['popup_end_at'] ?? ''));
    if ($startRaw === '' && $endRaw === '') return 'always';
    if ($startRaw === '' || $endRaw === '') return 'incomplete';

    $start = popup_parse_datetime($startRaw);
    $end = popup_parse_datetime($endRaw);
    if (!$start || !$end || $end < $start) return 'invalid';
    $now = ($now ?? new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))
        ->setTimezone(new DateTimeZone('Europe/Berlin'));

    if ($now < $start) return 'upcoming';
    if ($now > $end->modify('+59 seconds')) return 'expired';
    return 'active';
}

function popup_should_render(array $home, ?DateTimeImmutable $now=null): bool {
    // Vor Beginn kann das versteckte HTML bereits auf der Seite stehen:
    // ein offener Browsertab aktiviert es dann pünktlich per JavaScript.
    return in_array(popup_schedule_state($home, $now), ['always','upcoming','active'], true);
}

function popup_schedule_milliseconds(array $home): array {
    $start = popup_parse_datetime(trim((string)($home['popup_start_at'] ?? '')));
    $end = popup_parse_datetime(trim((string)($home['popup_end_at'] ?? '')));
    return [
        $start ? $start->getTimestamp()*1000 : 0,
        $end ? ($end->getTimestamp()+60)*1000-1 : 0,
    ];
}
