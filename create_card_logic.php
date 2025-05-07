<?php
// Stelle sicher, dass die Datei mit PHPQRcode korrekt eingebunden ist.
require_once __DIR__ . '/lib/phpqrcode/qrlib.php';
require __DIR__ . '/lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome");
    exit();
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) die("Nicht eingeloggt.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $year = $_POST['year'];
    $artist = $_POST['artist'];
    $songlink = $_POST['songlink'];
    $platform = 'Andere';

    if (strpos($songlink, 'deezer.com/track/') !== false) {
        $platform = 'Deezer';
    } elseif (preg_match('/spotify\.com\/.+\/track\//', $songlink) || strpos($songlink, 'spotify.com/track/') !== false) {
        $platform = 'Spotify';
    } elseif (strpos($songlink, 'youtube.com/watch?v=') !== false || 
              strpos($songlink, 'music.youtube.com/watch?v=') !== false || 
              strpos($songlink, 'youtu.be/') !== false) {
        $platform = 'YouTube';
    } else {
        $platform = 'Andere';
    }

    // Hintergrundbild zufällig auswählen
    $bgFiles = glob("assets/backgrounds/*.{jpg,JPG,jpeg,JPEG}", GLOB_BRACE);
    $bgFile = $bgFiles[array_rand($bgFiles)];
    $im = imagecreatefromstring(file_get_contents($bgFile));

    if (!$im) {
        die("Fehler: Hintergrundbild konnte nicht geladen werden.");
    }

    // Text auf das Bild schreiben
    $width = imagesx($im);
    $height = imagesy($im);

    $white = imagecolorallocate($im, 255, 255, 255);
    $font = 'fonts/maison-neue-bold.ttf';

    $sizeArtist = 30;
    $sizeYear = 120;
    $sizeTitle = 30;

    // Funktionen zum Zentrieren von Text
    function centerText($image, $text, $size, $y, $color, $font) {
        $bbox = imagettfbbox($size, 0, $font, $text);
        $textWidth = $bbox[2] - $bbox[0];
        $x = (imagesx($image) - $textWidth) / 2;
        imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
    }

    function centerMultilineTextIfLong($image, $text, $size, $startY, $lineHeight, $color, $font, $maxCharsPerLine = 26) {
        if (mb_strlen($text) > $maxCharsPerLine) {
            $lines = explode("\n", wordwrap($text, $maxCharsPerLine, "\n", true));
        } else {
            $lines = [$text];
        }

        $startY = $startY - 20 * (count($lines) - 1);

        foreach ($lines as $i => $line) {
            $bbox = imagettfbbox($size, 0, $font, $line);
            $textWidth = $bbox[2] - $bbox[0];
            $x = (imagesx($image) - $textWidth) / 2;
            $y = $startY + $i * $lineHeight;
            imagettftext($image, $size, 0, $x, $y, $color, $font, $line);
        }
    }

    // Text positionieren
    centerMultilineTextIfLong($im, $artist, $sizeArtist, 120, 35, $white, $font); 
    centerText($im, $year, $sizeYear, 360, $white, $font);                       
    centerMultilineTextIfLong($im, $title, $sizeTitle, 540, 35, $white, $font); 

    // Bild speichern
    $image_text = uniqid() . '_text.png';
    if (!imagepng($im, __DIR__ . "/cards/images/$image_text")) {
        die("Fehler: Bild konnte nicht gespeichert werden.");
    }

    imagedestroy($im);

    // QR-Code mit Hintergrund generieren
    $qrTemp = tempnam(sys_get_temp_dir(), 'qr');
    QRcode::png($songlink, $qrTemp, QR_ECLEVEL_H, 10, 0);

    // QR-Code laden
    $qrImage = imagecreatefrompng($qrTemp);
    if (!$qrImage) {
        die("Fehler: QR-Code konnte nicht geladen werden.");
    }

    // QR-Code invertieren
    imagefilter($qrImage, IMG_FILTER_NEGATE);

    // Originalgrößen und Skalierung berechnen
    $qrOrigWidth = imagesx($qrImage);
    $qrOrigHeight = imagesy($qrImage);

    $scale = 0.5;
    $qrNewWidth = $qrOrigWidth * $scale;
    $qrNewHeight = $qrOrigHeight * $scale;

    // Skalierte QR-Code-Bilder erzeugen
    $qrScaled = imagecreatetruecolor($qrNewWidth, $qrNewHeight);
    imagealphablending($qrScaled, false);
    imagesavealpha($qrScaled, true);
    $transparent = imagecolorallocatealpha($qrScaled, 0, 0, 0, 127);
    imagefill($qrScaled, 0, 0, $transparent);

    imagecopyresampled($qrScaled, $qrImage, 0, 0, 0, 0, $qrNewWidth, $qrNewHeight, $qrOrigWidth, $qrOrigHeight);

    // QR-Code-Hintergrund laden
    $qrBgPath = dirname(__DIR__) . "/hitster_customs/assets/qr_background.png";
    $qrBgImage = imagecreatefrompng($qrBgPath);
    if (!$qrBgImage) {
        die("Fehler: QR-Hintergrundbild konnte nicht geladen werden.");
    }

    // Zielposition des QR-Codes berechnen
    $bgWidth = imagesx($qrBgImage);
    $bgHeight = imagesy($qrBgImage);
    $destX = ($bgWidth - $qrNewWidth) / 2 - 5;
    $destY = ($bgHeight - $qrNewHeight) / 2 - 2;

    // QR-Code überlagern
    imagealphablending($qrBgImage, true);
    imagecopy($qrBgImage, $qrScaled, $destX, $destY, 0, 0, $qrNewWidth, $qrNewHeight);

    // QR-Code speichern
    $image_qr = uniqid() . '_qr.png';
    $qrSavePath = __DIR__ . "/cards/images/$image_qr";
    imagesavealpha($qrBgImage, true);
    imagepng($qrBgImage, $qrSavePath);

    // Aufräumen
    unlink($qrTemp);
    imagedestroy($qrImage);
    imagedestroy($qrScaled);
    imagedestroy($qrBgImage);

    // Wenn bereits alte Bilder existieren, löschen
    if (isset($old_image_text) && file_exists(__DIR__ . "/cards/images/$old_image_text")) {
        unlink(__DIR__ . "/cards/images/$old_image_text");
    }
    if (isset($old_image_qr) && file_exists(__DIR__ . "/cards/images/$old_image_qr")) {
        unlink(__DIR__ . "/cards/images/$old_image_qr");
    }

    // Neue Daten in der DB speichern
    $stmt = $pdo->prepare("INSERT INTO cards (user_id, title, year, artist, songlink, platform, image_text, image_qr, is_public, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");
    $stmt->execute([$userId, $title, $year, $artist, $songlink, $platform, $image_text, $image_qr]);

    header("Location: ../profile.php");
    exit;
}
?>
