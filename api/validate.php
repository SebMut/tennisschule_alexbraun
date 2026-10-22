<?php
declare(strict_types=1);
require __DIR__ . '/auth-lib.php';
require __DIR__ . '/content-validation.php';
require_auth();
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok'=>false,'error'=>'Methode nicht erlaubt.'],405);
$body=read_json_body();
$data=$body['data']??null;
if(!is_array($data)) json_response(['ok'=>false,'error'=>'Ungültige Daten.'],422);

json_response(['ok'=>true,'validation'=>validate_site_content($data)]);
