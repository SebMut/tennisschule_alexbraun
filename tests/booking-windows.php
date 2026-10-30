<?php
declare(strict_types=1);
require dirname(__DIR__) . '/_inc/site.php';
require dirname(__DIR__) . '/api/content-validation.php';

function expect_booking(bool $ok, string $message): void {
    if (!$ok) { fwrite(STDERR, "Fehler: ".$message."\n"); exit(1); }
}
$legacy = ['registration_start'=>'2025-01-01','registration_end'=>'2035-12-31','buttons'=>[]];
$oldButton = offer_button_as_offer($legacy, ['id'=>'old']);
expect_booking(offer_registration_state($oldButton)==='open','Älterer Button übernimmt historischen Trainingszeitraum');

$offer = ['buttons'=>[
    ['id'=>'past','registration_start'=>'2020-01-01','registration_end'=>'2020-12-31'],
    ['id'=>'current','registration_start'=>'2025-01-01','registration_end'=>'2035-12-31'],
]];
expect_booking(offer_registration_state($offer)==='open','Offenes Buttonfenster gilt für Angebot');
expect_booking(offer_registration_state(offer_button_as_offer($offer,$offer['buttons'][0]))==='closed','Abgelaufener Button bleibt geschlossen');
expect_booking(offer_registration_state(offer_button_as_offer($offer,$offer['buttons'][1]))==='open','Anderer Button unabhängig geöffnet');
expect_booking(validation_offer_state($offer)==='open','Inhaltsprüfung berücksichtigt einzelnen offenen Button');

$future = ['buttons'=>[
    ['registration_start'=>'2020-01-01','registration_end'=>'2020-12-31'],
    ['registration_start'=>'2030-01-01','registration_end'=>'2030-12-31'],
]];
expect_booking(offer_registration_state($future)==='upcoming','Angebot mit zukünftigem Button gilt als bald verfügbar');
expect_booking(offer_registration_start_label($future)==='01.01.2030','Frühestes künftiges Anmeldedatum wird angezeigt');
expect_booking(validation_offer_state($future)==='upcoming','Inhaltsprüfung meldet zukünftigen Button');
expect_booking(offer_registration_state(['buttons'=>[]])==='closed','Kein Button bedeutet geschlossen');

$input = [
    'offers'=>['items'=>[[
        'id'=>'sommer','registration_start'=>'2027-03-10','registration_end'=>'2027-04-18',
        'buttons'=>[
            ['label'=>'Individuell','registration_start'=>'2030-02-01','registration_end'=>'2030-03-01'],
            ['label'=>'Alt ohne Datumsfelder'],
        ]
    ]]],
    'trainers'=>['items'=>[]],
    'locations'=>[],
];
$normalized=normalize_site_content($input,[]);
$b=$normalized['offers']['items'][0]['buttons'];
expect_booking($b[0]['registration_start']==='2030-02-01','Individueller Zeitraum wird nie überschrieben');
expect_booking($b[1]['registration_start']==='2027-03-10','Fehlender Start wird aus Altdaten einmalig übernommen');
expect_booking($b[1]['registration_end']==='2027-04-18','Fehlendes Ende wird aus Altdaten einmalig übernommen');
echo "Buchungszeitraum-Tests erfolgreich.\n";
