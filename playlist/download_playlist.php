<?php
chdir(__DIR__ . '/..');
require 'lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Nicht eingeloggt.");
}

$playlistId = $_GET['id'] ?? null;
if (!$playlistId || !is_numeric($playlistId)) {
    die("Ungültige Playlist-ID.");
}

// Playlist-Daten
$stmt = $pdo->prepare("SELECT * FROM playlists WHERE id = ?");
$stmt->execute([$playlistId]);
$playlist = $stmt->fetch();

if (!$playlist || $playlist['user_id'] != $_SESSION['user_id']) {
    die("Zugriff verweigert.");
}

$playlistNameSanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $playlist['name']);

// Karten holen
$stmt = $pdo->prepare("SELECT c.* FROM playlist_cards pc JOIN cards c ON pc.card_id = c.id WHERE pc.playlist_id = ?");
$stmt->execute([$playlistId]);
$cards = $stmt->fetchAll();

if (empty($cards)) {
    die("Keine Karten in dieser Playlist.");
}

$tmpDir = sys_get_temp_dir() . '/playlist_' . uniqid();
mkdir($tmpDir);

// Für jede Karte: PNG erzeugen wie in download_card.php
foreach ($cards as $card) {
    $textPath = __DIR__ . "/../cards/images/" . $card['image_text'];
    $qrPath   = __DIR__ . "/../cards/images/" . $card['image_qr'];

    if (!file_exists($textPath) || !file_exists($qrPath)) continue;

    $finalImage = imagecreatetruecolor(1200, 600);
    imagesavealpha($finalImage, true);
    $transparent = imagecolorallocatealpha($finalImage, 0, 0, 0, 127);
    imagefill($finalImage, 0, 0, $transparent);

    $textImage = imagecreatefrompng($textPath);
    $qrImage   = imagecreatefrompng($qrPath);

    $textResized = imagecreatetruecolor(600, 600);
    imagealphablending($textResized, false);
    imagesavealpha($textResized, true);
    imagecopyresampled($textResized, $textImage, 0, 0, 0, 0, 600, 600, imagesx($textImage), imagesy($textImage));

    $qrResized = imagecreatetruecolor(600, 600);
    imagealphablending($qrResized, false);
    imagesavealpha($qrResized, true);
    imagecopyresampled($qrResized, $qrImage, 0, 0, 0, 0, 600, 600, imagesx($qrImage), imagesy($qrImage));

    imagecopy($finalImage, $textResized, 0, 0, 0, 0, 600, 600);
    imagecopy($finalImage, $qrResized, 600, 0, 0, 0, 600, 600);

    $fileName = $tmpDir . "/" . $card['title'] . " - " . $card['artist'] . ".png";
    imagepng($finalImage, $fileName);

    imagedestroy($finalImage);
    imagedestroy($textImage);
    imagedestroy($qrImage);
    imagedestroy($textResized);
    imagedestroy($qrResized);
}

// ZIP-Datei erstellen
$zipFile = sys_get_temp_dir() . '/' . $playlistNameSanitized . '.zip';
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("ZIP-Fehler.");
}

foreach (glob($tmpDir . '/*.png') as $file) {
    $zip->addFile($file, basename($file));
}
$zip->close();

// Download starten
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $playlistNameSanitized . '.zip"');
readfile($zipFile);

// Aufräumen
array_map('unlink', glob("$tmpDir/*.png"));
rmdir($tmpDir);
unlink($zipFile);
exit;
