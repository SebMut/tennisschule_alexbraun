<?php
declare(strict_types=1);

function content_revision_from_bytes(string $bytes): string {
    return hash('sha256', $bytes);
}
function content_lower(string $value): string {
    return function_exists('mb_strtolower') ? mb_strtolower($value,'UTF-8') : strtolower($value);
}

function normalize_site_content(array $data, array $current = []): array {
    $currentOffers = [];
    foreach (($current['offers']['items'] ?? []) as $offer) {
        $id = (string)($offer['id'] ?? '');
        if ($id !== '') $currentOffers[$id] = $offer;
    }

    foreach (($data['offers']['items'] ?? []) as &$offer) {
        $id = (string)($offer['id'] ?? '');
        $old = $currentOffers[$id] ?? [];

        $offer['registration_start'] = trim((string)(
            $offer['registration_start']
            ?? $offer['booking_date']
            ?? $old['registration_start']
            ?? $old['booking_date']
            ?? ''
        ));
        $offer['registration_end'] = trim((string)(
            $offer['registration_end']
            ?? $old['registration_end']
            ?? ''
        ));
        $offer['featured'] = !empty($offer['featured']);

        // Alte doppelte Buchungszeiträume je Button beim nächsten Speichern bereinigen.
        if (isset($offer['buttons']) && is_array($offer['buttons'])) {
            foreach ($offer['buttons'] as &$button) {
                if (is_array($button)) unset($button['registration_start'], $button['registration_end']);
            }
            unset($button);
        }

        unset(
            $offer['status'],
            $offer['status_kind'],
            $offer['registration_open'],
            $offer['booking_date'],
            $offer['unavailable_message']
        );
    }
    unset($offer);

    $currentTrainersByName = [];
    foreach (($current['trainers']['items'] ?? []) as $trainer) {
        $name = trim((string)($trainer['name'] ?? ''));
        if ($name !== '') $currentTrainersByName[$name] = $trainer;
    }
    foreach (($data['trainers']['items'] ?? []) as &$trainer) {
        if (!array_key_exists('visible', $trainer)) {
            $name = trim((string)($trainer['name'] ?? ''));
            $trainer['visible'] = array_key_exists('visible', $currentTrainersByName[$name] ?? [])
                ? (bool)$currentTrainersByName[$name]['visible']
                : true;
        } else {
            $trainer['visible'] = (bool)$trainer['visible'];
        }
    }
    unset($trainer);

    $data['locations']['map_route_button'] = (string)($data['locations']['map_route_button']
        ?? $current['locations']['map_route_button']
        ?? 'Route mit Mapbox berechnen');
    $data['locations']['map_route_loading'] = (string)($data['locations']['map_route_loading']
        ?? $current['locations']['map_route_loading']
        ?? 'Standort wird ermittelt …');
    $data['locations']['map_route_error'] = (string)($data['locations']['map_route_error']
        ?? $current['locations']['map_route_error']
        ?? 'Dein Standort konnte nicht ermittelt werden. Bitte erlaube den Standortzugriff und versuche es erneut.');

    $mapDefaults = [
        'feldkirchen' => ['latitude'=>48.143427223206544,'longitude'=>11.719520683624244],
        'heimstetten' => ['latitude'=>48.16306908434989,'longitude'=>11.74709917555206],
    ];
    foreach ($mapDefaults as $key => $coords) {
        $currentLocation = $current['locations'][$key] ?? [];
        $data['locations'][$key]['latitude'] = (float)($data['locations'][$key]['latitude']
            ?? $currentLocation['latitude']
            ?? $coords['latitude']);
        $data['locations'][$key]['longitude'] = (float)($data['locations'][$key]['longitude']
            ?? $currentLocation['longitude']
            ?? $coords['longitude']);
        unset($data['locations'][$key]['map_query'], $data['locations'][$key]['marker_link']);
    }
    unset($data['locations']['map_consent_text'], $data['locations']['map_consent_button']);

    return $data;
}

function validation_offer_state(array $offer): string {
    $tz = new DateTimeZone('Europe/Berlin');
    $now = new DateTimeImmutable('now', $tz);
    $startRaw = trim((string)($offer['registration_start'] ?? ''));
    $endRaw = trim((string)($offer['registration_end'] ?? ''));
    $start = preg_match('/^\d{4}-\d{2}-\d{2}$/', $startRaw)
        ? DateTimeImmutable::createFromFormat('!Y-m-d', $startRaw, $tz)
        : false;
    $end = preg_match('/^\d{4}-\d{2}-\d{2}$/', $endRaw)
        ? DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $endRaw.' 23:59:59', $tz)
        : false;

    if (!$start || !$end) return 'invalid';
    if ($now < $start) return 'upcoming';
    if ($now > $end) return 'closed';
    return 'open';
}

function validate_site_content(array $data): array {
    $issues = [];
    $add = static function(string $severity, string $section, string $code, string $message, string $path = '') use (&$issues): void {
        $issues[] = compact('severity','section','code','message','path');
    };

    if (trim((string)($data['site']['hero_image'] ?? '')) === '') {
        $add('error','allgemein','hero_image_missing','Hero-Bild fehlt.','site.hero_image');
    }

    $offersById = [];
    foreach (($data['offers']['items'] ?? []) as $i => $offer) {
        $id = (string)($offer['id'] ?? '');
        $title = trim((string)($offer['title'] ?? 'Angebot'));
        if ($id !== '') $offersById[$id] = $offer;

        $startRaw = trim((string)($offer['registration_start'] ?? ''));
        $endRaw = trim((string)($offer['registration_end'] ?? ''));
        if ($startRaw === '') {
            $add('error','angebote','offer_start_missing',$title.': Startdatum der Anmeldung fehlt.',"offers.items.$i.registration_start");
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$startRaw)) {
            $add('error','angebote','offer_start_invalid',$title.': Startdatum ist ungültig.',"offers.items.$i.registration_start");
        }
        if ($endRaw === '') {
            $add('error','angebote','offer_end_missing',$title.': Enddatum der Anmeldung fehlt.',"offers.items.$i.registration_end");
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$endRaw)) {
            $add('error','angebote','offer_end_invalid',$title.': Enddatum ist ungültig.',"offers.items.$i.registration_end");
        }
        if (
            preg_match('/^\d{4}-\d{2}-\d{2}$/',$startRaw)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/',$endRaw)
            && $endRaw < $startRaw
        ) {
            $add('error','angebote','offer_date_order',$title.': Enddatum liegt vor dem Startdatum.',"offers.items.$i.registration_end");
        }

        $state = validation_offer_state($offer);
        if (isset($offer['buttons']) && is_array($offer['buttons'])) {
            if (count($offer['buttons']) > 12) {
                $add('error','angebote','offer_buttons_limit',$title.': Maximal 12 Buchungsbuttons.',"offers.items.$i.buttons");
            }
            foreach ($offer['buttons'] as $j => $button) {
                if (!is_array($button)) {
                    $add('error','angebote','offer_button_invalid',$title.': Ungültiger Button.',"offers.items.$i.buttons.$j");
                    continue;
                }
                $buttonLabel = trim((string)($button['label'] ?? ''));
                $buttonUrl = trim((string)($button['url'] ?? ''));
                $buttonPath = "offers.items.$i.buttons.$j";
                if ($buttonLabel === '') {
                    $add('error','angebote','offer_button_label_missing',$title.': Button '.($j+1).' braucht einen Text.',"$buttonPath.label");
                }
                if ($buttonUrl !== '' && !preg_match('~^(?:https?://[^\s]+|/(?!/)[^\s]*)$~i',$buttonUrl)) {
                    $add('error','angebote','offer_button_url_invalid',$title.': Button '.($j+1).' hat eine ungültige URL.',"$buttonPath.url");
                }
                // Die Buchbarkeit wird ausschließlich vom Trainingszeitraum bestimmt.
                $buttonState=$state;
                if ($buttonState === 'open' && $buttonUrl === '') {
                    $add('error','angebote','offer_button_url_missing',$title.': Buchbarer Button '.($j+1).' braucht einen Ziel-Link.',"$buttonPath.url");
                }
                if (!in_array((string)($button['mode']??'modal'),['modal','link'],true)) {
                    $add('error','angebote','offer_button_mode_invalid',$title.': Ungültige Button-Aktion.',"$buttonPath.mode");
                }
            }
        } else {
            if ($state === 'open') {
                if ($id === 'camps') {
                    if (trim((string)($offer['form_url_feldkirchen'] ?? '')) === '') {
                        $add('error','angebote','camp_form_feldkirchen_missing',$title.': Anmeldung ist geöffnet, aber die Formular-URL für Feldkirchen fehlt.',"offers.items.$i.form_url_feldkirchen");
                    }
                    if (trim((string)($offer['form_url_heimstetten'] ?? '')) === '') {
                        $add('error','angebote','camp_form_heimstetten_missing',$title.': Anmeldung ist geöffnet, aber die Formular-URL für Heimstetten fehlt.',"offers.items.$i.form_url_heimstetten");
                    }
                } elseif (trim((string)($offer['form_url'] ?? '')) === '') {
                    $add('error','angebote','offer_form_missing',$title.': Anmeldung ist geöffnet, aber es ist keine Formular-URL hinterlegt.',"offers.items.$i.form_url");
                }
            }
        }

        foreach (['form_url','form_url_feldkirchen','form_url_heimstetten'] as $field) {
            $url = trim((string)($offer[$field] ?? ''));
            if ($url !== '' && !filter_var($url,FILTER_VALIDATE_URL)) {
                $add('warning','angebote','offer_form_url_invalid',$title.': Eine Formular-URL wirkt ungültig.',"offers.items.$i.$field");
            }
        }
    }

    // Popup status validation is offer-aware. It detects the referenced offer from
    // heading/text instead of assuming Wintertraining. Generic popups are not checked.
    if (!empty($data['home']['popup_enabled'])) {
        $popupText = content_lower((string)($data['home']['popup_text'] ?? ''));
        $popupHeading = content_lower((string)($data['home']['popup_heading'] ?? ''));
        $popupContent = trim($popupHeading.' '.$popupText);
        $matchedOffer = null;
        foreach ($offersById as $id => $offer) {
            $title = trim((string)($offer['title'] ?? ''));
            $needles = array_filter([
                content_lower($title),
                $id === 'sommer' ? 'sommertraining' : '',
                $id === 'winter' ? 'wintertraining' : '',
                $id === 'camps' ? 'tenniscamp' : '',
                $id === 'camps' ? 'tenniscamps' : '',
            ]);
            foreach ($needles as $needle) {
                if ($needle !== '' && str_contains($popupContent, $needle)) {
                    $matchedOffer = $offer;
                    break 2;
                }
            }
        }

        if (is_array($matchedOffer)) {
            $title = trim((string)($matchedOffer['title'] ?? 'Angebot'));
            $state = validation_offer_state($matchedOffer);
            if (str_contains($popupText,'geöffnet') && $state !== 'open') {
                $add('error','home','popup_offer_mismatch','Popup sagt „Anmeldung geöffnet“, '.$title.' ist laut Datumsfenster aber nicht geöffnet.','home.popup_text');
            }
            if (str_contains($popupText,'geschlossen') && $state === 'open') {
                $add('error','home','popup_offer_mismatch','Popup sagt „Anmeldung geschlossen“, '.$title.' ist laut Datumsfenster aber geöffnet.','home.popup_text');
            }
        }
    }

    $seenTrainerNames = [];
    foreach (($data['trainers']['items'] ?? []) as $i => $trainer) {
        $name = trim((string)($trainer['name'] ?? ''));
        if ($name !== '') {
            $key = content_lower($name);
            if (isset($seenTrainerNames[$key])) {
                $add('warning','trainerteam','trainer_duplicate','Trainername „'.$name.'“ kommt mehrfach vor.',"trainers.items.$i.name");
            }
            $seenTrainerNames[$key] = true;
        }
        if (($trainer['visible'] ?? true) !== false) {
            if ($name === '') $add('error','trainerteam','trainer_name_missing','Ein sichtbarer Trainer hat keinen Namen.',"trainers.items.$i.name");
            $image = trim((string)($trainer['image'] ?? ''));
            if ($image === '') {
                $add('error','trainerteam','trainer_image_missing',($name!==''?$name:'Trainer').': Bild fehlt.',"trainers.items.$i.image");
            } elseif ($image === '/assets/media/logo.png' && content_lower($name) !== 'julius brand') {
                $add('warning','trainerteam','trainer_placeholder_image',($name!==''?$name:'Trainer').': Es wird noch das Logo als Platzhalterbild verwendet.',"trainers.items.$i.image");
            }
            if (content_lower($name) === 'test' || content_lower(trim((string)($trainer['role'] ?? ''))) === 'test') {
                $add('warning','trainerteam','trainer_test_content','Ein sichtbarer Test-Trainer ist noch vorhanden.',"trainers.items.$i.name");
            }
        }
    }

    $errors = count(array_filter($issues, static fn($i) => $i['severity'] === 'error'));
    $warnings = count(array_filter($issues, static fn($i) => $i['severity'] === 'warning'));
    return ['ok'=>$errors===0,'errors'=>$errors,'warnings'=>$warnings,'issues'=>$issues];
}
