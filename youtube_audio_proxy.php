<?php
// Sicherheitsabfrage
if (!isset($_GET['url']) || !filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo "Ungültiger oder fehlender URL-Parameter.";
    exit;
}

$url = escapeshellarg($_GET['url']);
$tmpFile = tempnam(sys_get_temp_dir(), 'yt_') . '.m4a';

// Pfad zur yt-dlp.exe
$ytDlpPath = __DIR__ . DIRECTORY_SEPARATOR . 'yt-dlp.exe';
$ytDlpPath = escapeshellarg($ytDlpPath);

// Befehl zusammenbauen
$cmd = "$ytDlpPath -f bestaudio[ext=m4a] --max-filesize 10M -o \"$tmpFile\" -x --audio-format m4a --quiet $url";

// Kommando ausführen
exec($cmd, $output, $returnVar);

// Prüfung
if ($returnVar !== 0 || !file_exists($tmpFile)) {
    http_response_code(500);
    echo "Fehler beim Extrahieren des Audios.";
    exit;
}

// Audio zurückgeben
header('Content-Type: audio/mp4');
header('Content-Disposition: inline; filename="preview.m4a"');
header('Content-Length: ' . filesize($tmpFile));
readfile($tmpFile);
unlink($tmpFile);
exit;
