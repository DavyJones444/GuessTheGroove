<?php
require 'lib/db.php';
require 'vendor/autoload.php'; // ← Für FPDF

// Playlist-ID prüfen
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Playlist-ID.");
}

$playlistId = (int)$_GET['id'];

// Beispielhafter Query – bitte ggf. anpassen!
$stmt = $pdo->prepare("
    SELECT c.image_text, c.image_qr 
    FROM cards c
    JOIN playlist_cards pc ON pc.card_id = c.id
    WHERE pc.playlist_id = ?
");
$stmt->execute([$playlistId]);
$cards = $stmt->fetchAll();

if (!$cards) {
    die("Keine Karten gefunden.");
}

// PDF initialisieren
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// Layout-Konstanten
$cardWidth = 100;  // in mm
$cardHeight = 50;  // in mm
$cardsPerRow = 2;
$cardsPerColumn = 5;
$marginX = 5;
$marginY = 5;
$spacingX = 0;
$spacingY = 0;

$currentRow = 0;
$currentColumn = 0;

foreach ($cards as $index => $card) {
    $textPath = __DIR__ . "/cards/images/" . $card['image_text'];
    $qrPath = __DIR__ . "/cards/images/" . $card['image_qr'];

    if (!file_exists($textPath) || !file_exists($qrPath)) {
        continue; // Überspringen, wenn Bild fehlt
    }

    // Temporäres Kombibild erzeugen
    $combinedImage = imagecreatetruecolor(1200, 600);
    imagesavealpha($combinedImage, true);
    $transparent = imagecolorallocatealpha($combinedImage, 0, 0, 0, 127);
    imagefill($combinedImage, 0, 0, $transparent);

    $textImage = imagecreatefrompng($textPath);
    $qrImage = imagecreatefrompng($qrPath);

    imagecopyresampled($combinedImage, $textImage, 0, 0, 0, 0, 600, 600, imagesx($textImage), imagesy($textImage));
    imagecopyresampled($combinedImage, $qrImage, 600, 0, 0, 0, 600, 600, imagesx($qrImage), imagesy($qrImage));

    // Zwischenspeichern
    $tempFile = tempnam(sys_get_temp_dir(), 'card_') . '.png';
    imagepng($combinedImage, $tempFile);

    // Position auf der PDF-Seite
    $x = $marginX + $currentColumn * ($cardWidth + $spacingX);
    $y = $marginY + $currentRow * ($cardHeight + $spacingY);
    $pdf->Image($tempFile, $x, $y, $cardWidth, $cardHeight);

    // Aufräumen
    unlink($tempFile);
    imagedestroy($textImage);
    imagedestroy($qrImage);
    imagedestroy($combinedImage);

    // Position aktualisieren
    $currentColumn++;
    if ($currentColumn >= $cardsPerRow) {
        $currentColumn = 0;
        $currentRow++;
    }

    if ($currentRow >= $cardsPerColumn) {
        $pdf->AddPage();
        $currentRow = 0;
        $currentColumn = 0;
    }
}

// Ausgabe
$pdf->Output('D', "playlist_{$playlistId}.pdf");
exit;
