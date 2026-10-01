<?php
declare(strict_types=1);
require __DIR__ . '/auth-lib.php';
require_auth();

$json = @file_get_contents(site_json_path());
if ($json === false) json_response(['ok'=>false,'error'=>'Inhaltsdatei konnte nicht gelesen werden.'], 500);
$data = json_decode($json, true);
if (!is_array($data)) json_response(['ok'=>false,'error'=>'Inhaltsdatei ist ungültig.'], 500);

json_response(['ok'=>true,'data'=>$data,'csrf'=>csrf_token()]);
