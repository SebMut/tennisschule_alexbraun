<?php
declare(strict_types=1);
require __DIR__ . '/auth-lib.php';
require __DIR__ . '/github.php';
require __DIR__ . '/content-validation.php';
require_auth();

function github_repo_base(): string {
    $cfg=app_config()['github'];
    return 'https://api.github.com/repos/'.rawurlencode((string)$cfg['owner']).'/'.rawurlencode((string)$cfg['repo']);
}

if($_SERVER['REQUEST_METHOD']==='GET'){
    $cfg=app_config()['github'];
    $url=github_repo_base().'/commits?path='.rawurlencode('data/site.json').'&sha='.rawurlencode((string)$cfg['branch']).'&per_page=20';
    try{
        $rows=github_api('GET',$url);
        $items=array_map(static fn($row)=>[
            'sha'=>(string)($row['sha']??''),
            'short_sha'=>substr((string)($row['sha']??''),0,8),
            'message'=>(string)($row['commit']['message']??'Inhaltsänderung'),
            'date'=>(string)($row['commit']['committer']['date']??''),
            'author'=>(string)($row['commit']['author']['name']??''),
        ],$rows);
        json_response(['ok'=>true,'revisions'=>$items]);
    }catch(Throwable $e){
        json_response(['ok'=>false,'error'=>'Versionen konnten nicht geladen werden: '.$e->getMessage()],502);
    }
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    require_csrf();
    $body=read_json_body();
    $sha=preg_replace('/[^a-f0-9]/i','',(string)($body['sha']??''));
    $expectedRevision=trim((string)($body['revision']??''));
    if(strlen($sha)<7 || strlen($sha)>40) json_response(['ok'=>false,'error'=>'Ungültige Version.'],422);

    $path=site_json_path();
    $currentBytes=is_file($path)?(string)file_get_contents($path):'';
    $currentRevision=content_revision_from_bytes($currentBytes);
    if($expectedRevision==='' || !hash_equals($currentRevision,$expectedRevision)){
        json_response([
            'ok'=>false,
            'error'=>'Die Inhalte wurden inzwischen geändert. Bitte zuerst neu laden.',
            'conflict'=>true,
            'current_revision'=>$currentRevision,
        ],409);
    }
    $current=json_decode($currentBytes,true);
    if(!is_array($current)) $current=[];

    try{
        $url=github_repo_base().'/contents/'.github_encoded_path('data/site.json').'?ref='.rawurlencode($sha);
        $file=github_api('GET',$url);
        $encoded=str_replace(["\r","\n"],'',(string)($file['content']??''));
        $bytes=base64_decode($encoded,true);
        if($bytes===false) throw new RuntimeException('Historische Datei konnte nicht gelesen werden.');
        $restored=json_decode($bytes,true);
        if(!is_array($restored)) throw new RuntimeException('Historische Inhaltsdatei ist ungültig.');

        $restored=normalize_site_content($restored,$current);
        $validation=validate_site_content($restored);
        if(($validation['errors']??0)>0){
            json_response([
                'ok'=>false,
                'error'=>'Diese alte Version kann nicht direkt wiederhergestellt werden, weil sie mit den aktuellen Inhaltsregeln kollidiert.',
                'validation'=>$validation,
            ],422);
        }

        $json=json_encode($restored,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n";
        $tmp=$path.'.tmp-'.bin2hex(random_bytes(4));
        if(@file_put_contents($tmp,$json,LOCK_EX)===false || !@rename($tmp,$path)){
            @unlink($tmp);
            throw new RuntimeException('Wiederherstellung auf dem Webspace fehlgeschlagen.');
        }

        $commit=github_put_file('data/site.json',$json,'Restore website content from '.$sha);
        json_response([
            'ok'=>true,
            'revision'=>content_revision_from_bytes($json),
            'validation'=>$validation,
            'commit'=>$commit,
        ]);
    }catch(Throwable $e){
        json_response(['ok'=>false,'error'=>'Wiederherstellung fehlgeschlagen: '.$e->getMessage()],502);
    }
}

json_response(['ok'=>false,'error'=>'Methode nicht erlaubt.'],405);
