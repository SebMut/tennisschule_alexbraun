<?php
declare(strict_types=1);
require __DIR__ . '/auth-lib.php';
require __DIR__ . '/newsletter-lib.php';
require_auth();

if($_SERVER['REQUEST_METHOD']==='GET' && ($_GET['format']??'')==='csv'){
    $items=newsletter_load();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="newsletter-abonnenten-'.date('Y-m-d').'.csv"');
    echo "\xEF\xBB\xBF";
    $out=fopen('php://output','w');
    fputcsv($out,['Name','E-Mail','Status','Angelegt','Bestätigt','Abgemeldet'],';');
    foreach($items as $item){
      fputcsv($out,[
        $item['name']??'',$item['email']??'',$item['status']??'',
        $item['created_at']??'',$item['confirmed_at']??'',$item['unsubscribed_at']??''
      ],';');
    }
    fclose($out); exit;
}

if($_SERVER['REQUEST_METHOD']==='GET'){
    $items=newsletter_load();
    usort($items,fn($a,$b)=>strcmp((string)($b['created_at']??''),(string)($a['created_at']??'')));
    $counts=['all'=>count($items),'active'=>0,'pending'=>0,'unsubscribed'=>0];
    foreach($items as $item){
      $status=(string)($item['status']??'pending');
      if(isset($counts[$status]))$counts[$status]++;
    }
    json_response(['ok'=>true,'counts'=>$counts,'subscribers'=>array_map(fn($i)=>[
      'name'=>$i['name']??'','email'=>$i['email']??'','status'=>$i['status']??'pending',
      'created_at'=>$i['created_at']??'','confirmed_at'=>$i['confirmed_at']??'','unsubscribed_at'=>$i['unsubscribed_at']??''
    ],$items)]);
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    require_csrf();
    $body=read_json_body();
    $email=newsletter_normalize_email((string)($body['email']??''));
    $action=(string)($body['action']??'');
    if(!filter_var($email,FILTER_VALIDATE_EMAIL))json_response(['ok'=>false,'error'=>'Ungültige E-Mail-Adresse.'],422);
    $items=newsletter_load(); $idx=newsletter_find_index($items,$email);
    if($idx<0)json_response(['ok'=>false,'error'=>'Eintrag nicht gefunden.'],404);
    if($action==='delete'){
      array_splice($items,$idx,1); newsletter_save($items); json_response(['ok'=>true]);
    }
    if($action==='unsubscribe'){
      $items[$idx]['status']='unsubscribed'; $items[$idx]['unsubscribed_at']=date(DATE_ATOM); $items[$idx]['updated_at']=date(DATE_ATOM);
      newsletter_save($items); json_response(['ok'=>true]);
    }
    json_response(['ok'=>false,'error'=>'Unbekannte Aktion.'],422);
}
json_response(['ok'=>false],405);
