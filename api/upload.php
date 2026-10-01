<?php
declare(strict_types=1);
require __DIR__ . '/auth-lib.php';
require __DIR__ . '/github.php';
require_auth();
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok'=>false,'error'=>'Methode nicht erlaubt.'], 405);
if (empty($_FILES['file']) || !is_array($_FILES['file'])) json_response(['ok'=>false,'error'=>'Keine Datei empfangen.'], 422);

$file = $_FILES['file'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) json_response(['ok'=>false,'error'=>'Upload fehlgeschlagen.'], 422);
if ((int)$file['size'] <= 0 || (int)$file['size'] > 8 * 1024 * 1024) json_response(['ok'=>false,'error'=>'Bild darf maximal 8 MB groß sein.'], 422);

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file((string)$file['tmp_name']);
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];
if (!isset($allowed[$mime])) json_response(['ok'=>false,'error'=>'Erlaubt sind JPG, PNG und WebP.'], 422);

$filename = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
$relative = 'assets/media/cms/' . $filename;
$targetDir = dirname(__DIR__) . '/assets/media/cms';
if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true)) json_response(['ok'=>false,'error'=>'Upload-Verzeichnis konnte nicht erstellt werden.'], 500);
$target = $targetDir . '/' . $filename;

if (!move_uploaded_file((string)$file['tmp_name'], $target)) json_response(['ok'=>false,'error'=>'Bild konnte nicht gespeichert werden.'], 500);
$bytes = (string)file_get_contents($target);

$github = ['ok'=>false,'commit'=>null,'warning'=>null];
try {
    $github['commit'] = github_put_file($relative, $bytes, 'Upload CMS image ' . $filename);
    $github['ok'] = true;
} catch (Throwable $e) {
    $github['warning'] = $e->getMessage();
}

json_response(['ok'=>true,'path'=>'/' . $relative,'github'=>$github]);
