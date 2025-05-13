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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch'])) {

    // Playlist anhand des ersten Tracks erstellen
    $playlist = $_POST['playlist'] ?? [];
    $playlistName = $playlist['name'] ?? 'Neue Playlist';

    // Playlist erstellen
    $stmt = $pdo->prepare("INSERT INTO playlists (user_id, name) VALUES (?, ?)");
    $stmt->execute([$userId, $playlistName]);
    $playlistId = $pdo->lastInsertId();

    foreach ($_POST['tracks'] as $track) {
        $title = $track['title'] ?? 'Unbekannt';
        $artist = $track['artist'] ?? 'Unbekannt';
        $year = $track['year'] ?? '0000';
        $songlink = $track['songlink'] ?? '';

        $cardId = generateCard(   $userId, $title, $artist, $year, $songlink, $pdo);
        
        // Karte zur Playlist hinzufügen
        $stmt = $pdo->prepare("INSERT INTO playlist_cards (playlist_id, card_id) VALUES (?, ?)");
        $stmt->execute([$playlistId, $cardId]);
    }

    header("Location: ../profile?batch=1");
    exit();
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $year = $_POST['year'];
    $artist = $_POST['artist'];
    $songlink = $_POST['songlink'];

    generateCard(   $userId, $title, $artist, $year, $songlink, $pdo);

    header("Location: ../profile.php");
    exit;
}

// Hilfsfunktionen
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

function generateCard($userId, $title, $artist, $year, $songlink, $pdo) {
    require_once __DIR__ . '/lib/phpqrcode/qrlib.php';

    // Plattform erkennen
    $platform = 'Andere';
    if (strpos($songlink, 'deezer.com/track/') !== false) {
        $platform = 'Deezer';
    } elseif (strpos($songlink, 'deezer.com/playlist/') !== false) {
        $platform = 'Deezer Playlist';
    } elseif (preg_match('/spotify\.com\/track\//', $songlink)) {
        $platform = 'Spotify';
    } elseif (preg_match('/spotify\.com\/playlist\//', $songlink)) {
        $platform = 'Spotify Playlist';
    } elseif (strpos($songlink, 'youtube.com/watch?v=') !== false ||
              strpos($songlink, 'music.youtube.com/watch?v=') !== false ||
              strpos($songlink, 'youtu.be/') !== false) {
        $platform = 'YouTube';
    }

    // Hintergrundbild zufällig auswählen
    $bgFiles = glob("assets/backgrounds/*.{jpg,JPG,jpeg,JPEG}", GLOB_BRACE);
    $bgFile = $bgFiles[array_rand($bgFiles)];
    $im = imagecreatefromstring(file_get_contents($bgFile));
    if (!$im) die("Fehler: Hintergrundbild konnte nicht geladen werden.");

    // Bildmaße und Farben
    $white = imagecolorallocate($im, 255, 255, 255);
    $font = 'fonts/maison-neue-bold.ttf';
    $sizeArtist = 30;
    $sizeYear = 120;
    $sizeTitle = 30;

    // Text auf Bild
    centerMultilineTextIfLong($im, $artist, $sizeArtist, 120, 35, $white, $font);
    centerText($im, $year, $sizeYear, 360, $white, $font);
    centerMultilineTextIfLong($im, $title, $sizeTitle, 540, 35, $white, $font);

    // Text-Bild speichern
    $image_text = uniqid() . '_text.png';
    imagepng($im, __DIR__ . "/cards/images/$image_text");
    imagedestroy($im);

    // QR-Code generieren
    $qrTemp = tempnam(sys_get_temp_dir(), 'qr');
    QRcode::png($songlink, $qrTemp, QR_ECLEVEL_H, 10, 0);
    $qrImage = imagecreatefrompng($qrTemp);
    imagefilter($qrImage, IMG_FILTER_NEGATE);

    $qrNewWidth = 250;
    $qrNewHeight = 250;
    $qrScaled = imagecreatetruecolor($qrNewWidth, $qrNewHeight);
    imagealphablending($qrScaled, false);
    imagesavealpha($qrScaled, true);
    $transparent = imagecolorallocatealpha($qrScaled, 0, 0, 0, 127);
    imagefill($qrScaled, 0, 0, $transparent);
    imagecopyresampled($qrScaled, $qrImage, 0, 0, 0, 0, $qrNewWidth, $qrNewHeight, imagesx($qrImage), imagesy($qrImage));
    imagedestroy($qrImage);

    // QR-Hintergrund
    $qrBgPath = dirname(__DIR__) . "/hitster_customs/assets/qr_background.png";
    $qrBgImage = imagecreatefrompng($qrBgPath);
    $destX = (imagesx($qrBgImage) - $qrNewWidth) / 2 - 5;
    $destY = (imagesy($qrBgImage) - $qrNewHeight) / 2 - 2;
    imagecopy($qrBgImage, $qrScaled, $destX, $destY, 0, 0, $qrNewWidth, $qrNewHeight);
    imagedestroy($qrScaled);

    // QR-Bild speichern
    $image_qr = uniqid() . '_qr.png';
    imagepng($qrBgImage, __DIR__ . "/cards/images/$image_qr");
    imagedestroy($qrBgImage);
    unlink($qrTemp);
    // Wenn bereits alte Bilder existieren, löschen
    if (isset($old_image_text) && file_exists(__DIR__ . "/cards/images/$old_image_text")) {
        unlink(__DIR__ . "/cards/images/$old_image_text");
    }
    if (isset($old_image_qr) && file_exists(__DIR__ . "/cards/images/$old_image_qr")) {
        unlink(__DIR__ . "/cards/images/$old_image_qr");
    }

    // Datenbank speichern
    $stmt = $pdo->prepare("INSERT INTO cards (user_id, title, year, artist, songlink, platform, image_text, image_qr, is_public, created_at)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");
    $stmt->execute([$userId, $title, $year, $artist, $songlink, $platform, $image_text, $image_qr]);

    return $pdo->lastInsertId(); // gibt die ID der Karte zurück
}
?>
