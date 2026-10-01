<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok'=>false],405);
$ua = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
if ($ua !== '' && preg_match('/bot|crawler|spider|slurp|bingpreview|facebookexternalhit|headless/i', $ua)) {
    json_response(['ok'=>true,'bot'=>true]);
}

$siteFile=site_json_path();
$siteData=is_file($siteFile)?json_decode((string)file_get_contents($siteFile),true):[];
if (!is_array($siteData) || !($siteData['analytics']['enabled'] ?? true)) json_response(['ok'=>true,'disabled'=>true]);

$body=read_json_body();
$event=substr(preg_replace('/[^a-z0-9_:-]/i','',(string)($body['event']??'')),0,80);
$key=substr(preg_replace('/[^a-z0-9_:\-.]/i','',(string)($body['key']??$event)),0,120);
$path=substr((string)($body['path']??'/'),0,240);
$label=substr(trim((string)($body['label']??'')),0,160);
$href=substr(trim((string)($body['href']??'')),0,300);
$device=in_array(($body['device']??''),['mobile','tablet','desktop'],true)?$body['device']:'other';
$referrer=substr(strtolower((string)($body['referrer']??'direct')),0,120);
$session=substr((string)($body['session']??''),0,100);

if ($event==='') json_response(['ok'=>false],422);
if (!preg_match('#^/[A-Za-z0-9/_\-.]*$#',$path)) $path='/';

date_default_timezone_set('Europe/Berlin');
$day=date('Y-m-d');
$dir=storage_dir().'/stats';
$sessionDir=storage_dir().'/stats-sessions';
if(!is_dir($dir)) @mkdir($dir,0755,true);
if(!is_dir($sessionDir)) @mkdir($sessionDir,0755,true);

$file=$dir.'/'.$day.'.json';
$fp=fopen($file,'c+');
if(!$fp) json_response(['ok'=>false],500);
flock($fp,LOCK_EX);
$raw=stream_get_contents($fp);
$data=$raw?json_decode($raw,true):null;
if(!is_array($data)) $data=[
  'date'=>$day,'pageviews'=>0,'sessions'=>0,'pages'=>[],'page_sessions'=>[],
  'devices'=>[],'referrers'=>[],'events'=>[]
];

if($event==='pageview'){
  $data['pageviews']=(int)($data['pageviews']??0)+1;
  $data['pages'][$path]=(int)($data['pages'][$path]??0)+1;
  $data['devices'][$device]=(int)($data['devices'][$device]??0)+1;
  $data['referrers'][$referrer]=(int)($data['referrers'][$referrer]??0)+1;

  if($session!==''){
    $hash=hash('sha256',$day.'|'.$session);
    $sf=$sessionDir.'/'.$day.'.json';
    $seen=is_file($sf)?json_decode((string)file_get_contents($sf),true):[];
    if(!is_array($seen)) $seen=[];
    if(empty($seen['sessions'][$hash])){
      $seen['sessions'][$hash]=1;
      $data['sessions']=(int)($data['sessions']??0)+1;
    }
    $pageHash=hash('sha256',$day.'|'.$path.'|'.$session);
    if(empty($seen['pages'][$pageHash])){
      $seen['pages'][$pageHash]=1;
      $data['page_sessions'][$path]=(int)($data['page_sessions'][$path]??0)+1;
    }
    @file_put_contents($sf,json_encode($seen),LOCK_EX);
  }
}else{
  if(!isset($data['events'][$key])) $data['events'][$key]=['count'=>0,'event'=>$event,'label'=>$label,'href'=>$href,'path'=>$path];
  $data['events'][$key]['count']=(int)($data['events'][$key]['count']??0)+1;
  if($label!=='') $data['events'][$key]['label']=$label;
  if($href!=='') $data['events'][$key]['href']=$href;
  $data['events'][$key]['path']=$path;
}

rewind($fp); ftruncate($fp,0);
fwrite($fp,json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
fflush($fp); flock($fp,LOCK_UN); fclose($fp);

// Keep only short-lived anonymous session hashes; aggregated counts remain.
foreach(glob($sessionDir.'/*.json')?:[] as $old){
  if(filemtime($old)<time()-3*86400) @unlink($old);
}

// Retention for aggregate files.
$retention=max(30,min(730,(int)($siteData['analytics']['retention_days']??365)));
foreach(glob($dir.'/*.json')?:[] as $old){
  if(filemtime($old)<time()-$retention*86400) @unlink($old);
}
json_response(['ok'=>true]);
