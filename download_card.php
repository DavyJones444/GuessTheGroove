<?php
require 'lib/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ungültige Karten-ID.");
}

$cardId = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT image_text, image_qr FROM cards WHERE id = ?");
$stmt->execute([$cardId]);
$card = $stmt->fetch();

if (!$card) {
    die("Karte nicht gefunden.");
}

// Bildpfade
$textPath = __DIR__ . "/cards/images/" . $card['image_text'];
$qrPath = __DIR__ . "/cards/images/" . $card['image_qr'];

if (!file_exists($textPath) || !file_exists($qrPath)) {
    die("Bilder nicht gefunden.");
}

// Ausgangsbild mit 1200x600 erstellen
$finalImage = imagecreatetruecolor(1200, 600);

// Transparenz & Hintergrundfarbe setzen
imagesavealpha($finalImage, true);
$transparent = imagecolorallocatealpha($finalImage, 0, 0, 0, 127);
imagefill($finalImage, 0, 0, $transparent);

// Text- und QR-Bilder laden
$textImage = imagecreatefrompng($textPath);
$qrImage = imagecreatefrompng($qrPath);

// Zielgrößen berechnen und skalieren
$textResized = imagecreatetruecolor(600, 600);
imagealphablending($textResized, false);
imagesavealpha($textResized, true);
imagecopyresampled($textResized, $textImage, 0, 0, 0, 0, 600, 600, imagesx($textImage), imagesy($textImage));

$qrResized = imagecreatetruecolor(600, 600);
imagealphablending($qrResized, false);
imagesavealpha($qrResized, true);
imagecopyresampled($qrResized, $qrImage, 0, 0, 0, 0, 600, 600, imagesx($qrImage), imagesy($qrImage));

// Beides ins Hauptbild einfügen
imagecopy($finalImage, $textResized, 0, 0, 0, 0, 600, 600);
imagecopy($finalImage, $qrResized, 600, 0, 0, 0, 600, 600);

// Als PNG ausgeben
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="card_' . $cardId . '.png"');
imagepng($finalImage);

// Speicher freigeben
imagedestroy($finalImage);
imagedestroy($textImage);
imagedestroy($qrImage);
imagedestroy($textResized);
imagedestroy($qrResized);
?>
