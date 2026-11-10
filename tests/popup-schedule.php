<?php
declare(strict_types=1);
require dirname(__DIR__) . '/_inc/site.php';
require dirname(__DIR__) . '/api/content-validation.php';

function verify_popup(bool $ok, string $message): void {
    if (!$ok) {
        fwrite(STDERR,"Popup-Zeitplan-Test fehlgeschlagen: ".$message."\n");
        exit(1);
    }
}
$berlin = new DateTimeZone('Europe/Berlin');
$at = static fn(string $stamp): DateTimeImmutable => new DateTimeImmutable($stamp, $berlin);

$window = [
    'popup_enabled'=>true,
    'popup_start_at'=>'2035-02-01T10:30',
    'popup_end_at'=>'2035-02-03T18:00'
];
verify_popup(popup_schedule_state(['popup_enabled'=>false],$at('2035-02-02 13:00'))==='off','Manuell ausgeschaltet');
verify_popup(popup_schedule_state(['popup_enabled'=>true],$at('2035-02-02 13:00'))==='always','Altes Popup ohne Zeitraum bleibt nutzbar');
verify_popup(popup_should_render(['popup_enabled'=>true],$at('2035-02-02 13:00')),'Altes Popup wird eingebunden');
verify_popup(popup_schedule_state($window,$at('2035-02-01 10:29:59'))==='upcoming','Vor dem Start noch nicht offen');
verify_popup(popup_should_render($window,$at('2035-01-01 10:30:00')),'Zukünftiges Popup für offenen Browsertab vorbereiten');
verify_popup(popup_schedule_state($window,$at('2035-02-01 10:30:00'))==='active','Punktgenaue Aktivierung');
verify_popup(popup_schedule_state($window,$at('2035-02-03 18:00:59'))==='active','Volle Endminute ist gültig');
verify_popup(popup_schedule_state($window,$at('2035-02-03 18:01:00'))==='expired','Nach Endminute automatisch deaktiviert');
verify_popup(!popup_should_render($window,$at('2035-02-03 18:01:00')),'Abgelaufenes Popup auf neuen Seiten nicht eingebunden');
verify_popup(popup_schedule_state(['popup_enabled'=>true,'popup_start_at'=>'2035-02-01T10:00'],$at('2035-02-01 10:00'))==='incomplete','Nur ein Datum wird nicht aktiviert');
verify_popup(popup_schedule_state(['popup_enabled'=>true,'popup_start_at'=>'2035-02-02T10:00','popup_end_at'=>'2035-02-01T09:00'])==='invalid','Vertauschter Zeitraum wird abgelehnt');
verify_popup(popup_parse_datetime('2035-02-29T08:00')===null,'Ungültiger Kalendertag abgelehnt');
verify_popup(popup_parse_datetime('2026-03-29T02:30')===null,'Fehlende Stunde bei Sommerzeitwechsel abgelehnt');
verify_popup(popup_parse_datetime('2028-02-29T08:00') instanceof DateTimeImmutable,'Schalttag wird zugelassen');
[$startMs,$endMs] = popup_schedule_milliseconds($window);
verify_popup($startMs===$at('2035-02-01 10:30:00')->getTimestamp()*1000,'JavaScript-Startzeit');
verify_popup($endMs===$at('2035-02-03 18:01:00')->getTimestamp()*1000-1,'JavaScript-Endminute');

$base = [
    'site'=>['hero_image'=>'/assets/media/header_bild.webp'],
    'home'=>['popup_enabled'=>true, 'popup_start_at'=>'2035-02-01T10:30','popup_end_at'=>'2035-02-03T18:00'],
    'offers'=>['items'=>[]],
    'trainers'=>['items'=>[]],
    'locations'=>[]
];
$issues = validate_site_content($base)['issues'];
verify_popup(!array_filter($issues,static fn($issue)=>str_starts_with((string)$issue['code'],'popup_')),'Gültiger Zeitplan verursacht keinen Popup-Fehler');
$partial = $base;
$partial['home']['popup_end_at']='';
$issues = validate_site_content($partial)['issues'];
verify_popup(count(array_filter($issues,static fn($issue)=>$issue['code']==='popup_end_missing'))===1,'Fehlende Endzeit wird blockiert');
$wrong = $base;
$wrong['home']['popup_end_at']='2035-01-01T08:00';
$issues = validate_site_content($wrong)['issues'];
verify_popup(count(array_filter($issues,static fn($issue)=>$issue['code']==='popup_date_order'))===1,'Vertauschte Zeiten werden blockiert');

$old = normalize_site_content(['offers'=>['items'=>[]],'trainers'=>['items'=>[]],'locations'=>[]],[]);
verify_popup(($old['home']['popup_start_at']??'x')==='','Alte Inhalte erhalten ein leeres Startfeld');
verify_popup(($old['home']['popup_end_at']??'x')==='','Alte Inhalte erhalten ein leeres Endfeld');
$kept = normalize_site_content($base,[]);
verify_popup($kept['home']['popup_start_at']==='2035-02-01T10:30','Bestehender Start wird nicht überschrieben');

$futureOffer = $base;
$futureOffer['home']['popup_heading']='Wintertraining';
$futureOffer['home']['popup_text']='Die Anmeldung zum Wintertraining ist geöffnet.';
$futureOffer['offers']['items'] = [[
    'id'=>'winter','title'=>'Wintertraining',
    'registration_start'=>'2035-02-01','registration_end'=>'2035-02-28',
    'buttons'=>[['label'=>'Zur Anmeldung','registration_start'=>'2035-02-01','registration_end'=>'2035-02-28','url'=>'https://example.org/form']]
]];
$issues = validate_site_content($futureOffer)['issues'];
verify_popup(!array_filter($issues,static fn($issue)=>$issue['code']==='popup_offer_mismatch'),'Zukünftige Popup-Aussage zum geplanten Start prüfen');
echo "Popup-Zeitplan-Tests erfolgreich.\n";
