<?php
declare(strict_types=1);
require __DIR__ . '/auth-lib.php';
require_auth();

$days=max(1,min(365,(int)($_GET['days']??30)));
date_default_timezone_set('Europe/Berlin');
$start=(new DateTimeImmutable('today'))->modify('-'.($days-1).' days');
$dir=storage_dir().'/stats';

$out=[
 'period'=>['days'=>$days,'from'=>$start->format('Y-m-d'),'to'=>date('Y-m-d')],
 'totals'=>['pageviews'=>0,'sessions'=>0],
 'pages'=>[],'page_sessions'=>[],'devices'=>[],'referrers'=>[],'events'=>[],'daily'=>[]
];

for($i=0;$i<$days;$i++){
  $date=$start->modify("+{$i} days")->format('Y-m-d');
  $file=$dir.'/'.$date.'.json';
  $d=is_file($file)?json_decode((string)file_get_contents($file),true):[];
  if(!is_array($d)) $d=[];
  $pv=(int)($d['pageviews']??0); $ss=(int)($d['sessions']??0);
  $out['totals']['pageviews']+=$pv; $out['totals']['sessions']+=$ss;
  $out['daily'][]=['date'=>$date,'pageviews'=>$pv,'sessions'=>$ss];
  foreach(['pages','page_sessions','devices','referrers'] as $group){
    foreach(($d[$group]??[]) as $k=>$v) $out[$group][$k]=(int)($out[$group][$k]??0)+(int)$v;
  }
  foreach(($d['events']??[]) as $k=>$e){
    if(!isset($out['events'][$k])) $out['events'][$k]=['count'=>0,'event'=>$e['event']??'event','label'=>$e['label']??$k,'href'=>$e['href']??'','path'=>$e['path']??''];
    $out['events'][$k]['count']+=(int)($e['count']??0);
  }
}

arsort($out['pages']); arsort($out['page_sessions']); arsort($out['devices']); arsort($out['referrers']);
uasort($out['events'],fn($a,$b)=>($b['count']??0)<=>($a['count']??0));

json_response(['ok'=>true,'stats'=>$out]);
