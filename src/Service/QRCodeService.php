<?php
// src/Service/QRCodeService.php

// Wir binden die alte Lib ein (Pfad ggf. anpassen, wenn du sie verschoben hast)
require_once __DIR__ . '/../../lib/phpqrcode/qrlib.php'; 

class QRCodeService {
    
    private $uploadDir;
    private $fontPath;
    private $bgPath;

    public function __construct() {
        // Pfade definieren (Zeigen auf public/...)
        $this->uploadDir = __DIR__ . '/../../public/uploads/cards/';
        $this->fontPath = __DIR__ . '/../../public/assets/fonts/maison-neue-bold.ttf';
        $this->bgPath = __DIR__ . '/../../public/assets/backgrounds/';
    }

    public function generateCardImages($title, $artist, $year, $qrContent) {
        // 1. Hintergrund wählen
        $bgFiles = glob($this->bgPath . "*.{jpg,JPG,jpeg,JPEG}", GLOB_BRACE);
        if (!$bgFiles) throw new Exception("Keine Hintergründe gefunden.");
        
        $bgFile = $bgFiles[array_rand($bgFiles)];
        $im = imagecreatefromstring(file_get_contents($bgFile));

        // 2. Text schreiben
        $white = imagecolorallocate($im, 255, 255, 255);
        $this->centerMultilineTextIfLong($im, $artist, 30, 120, 35, $white);
        $this->centerText($im, $year, 120, 360, $white);
        $this->centerMultilineTextIfLong($im, $title, 30, 540, 35, $white);

        // Text-Bild speichern
        $imageTextName = uniqid() . '_text.png';
        imagepng($im, $this->uploadDir . $imageTextName);
        imagedestroy($im);

        // 3. QR-Code generieren
        $qrTemp = tempnam(sys_get_temp_dir(), 'qr');
        \QRcode::png($qrContent, $qrTemp, QR_ECLEVEL_H, 10, 0);
        $qrImage = imagecreatefrompng($qrTemp);
        imagefilter($qrImage, IMG_FILTER_NEGATE); // Invertieren

        // Skalieren & Transparent machen
        $qrNewWidth = 250; 
        $qrNewHeight = 250;
        $qrScaled = imagecreatetruecolor($qrNewWidth, $qrNewHeight);
        imagealphablending($qrScaled, false);
        imagesavealpha($qrScaled, true);
        $transparent = imagecolorallocatealpha($qrScaled, 0, 0, 0, 127);
        imagefill($qrScaled, 0, 0, $transparent);
        imagecopyresampled($qrScaled, $qrImage, 0, 0, 0, 0, $qrNewWidth, $qrNewHeight, imagesx($qrImage), imagesy($qrImage));
        imagedestroy($qrImage);

        // QR Hintergrund laden
        $qrBgImage = imagecreatefrompng(__DIR__ . '/../../public/assets/qr_background.png');
        $destX = (imagesx($qrBgImage) - $qrNewWidth) / 2 - 5;
        $destY = (imagesy($qrBgImage) - $qrNewHeight) / 2 - 2;
        imagecopy($qrBgImage, $qrScaled, $destX, $destY, 0, 0, $qrNewWidth, $qrNewHeight);
        imagedestroy($qrScaled);

        // QR-Bild speichern
        $imageQrName = uniqid() . '_qr.png';
        imagepng($qrBgImage, $this->uploadDir . $imageQrName);
        imagedestroy($qrBgImage);
        unlink($qrTemp);

        return [
            'image_text' => $imageTextName,
            'image_qr' => $imageQrName
        ];
    }

    public function deleteImages($textImg, $qrImg) {
        if ($textImg && file_exists($this->uploadDir . $textImg)) unlink($this->uploadDir . $textImg);
        if ($qrImg && file_exists($this->uploadDir . $qrImg)) unlink($this->uploadDir . $qrImg);
    }

    public function generateCompositeForDownload($textImgName, $qrImgName) {
        $textPath = $this->uploadDir . $textImgName;
        $qrPath = $this->uploadDir . $qrImgName;

        if (!file_exists($textPath) || !file_exists($qrPath)) return null;

        $finalImage = imagecreatetruecolor(1200, 600);
        imagesavealpha($finalImage, true);
        $transparent = imagecolorallocatealpha($finalImage, 0, 0, 0, 127);
        imagefill($finalImage, 0, 0, $transparent);

        $textImage = imagecreatefrompng($textPath);
        $qrImage = imagecreatefrompng($qrPath);

        // Resize auf 600x600 für beide Hälften
        $this->copyResampledTo($textImage, $finalImage, 0);
        $this->copyResampledTo($qrImage, $finalImage, 600);

        imagedestroy($textImage);
        imagedestroy($qrImage);

        return $finalImage;
    }

    // --- Private Hilfsmethoden (aus deiner Logic Datei) ---

    private function copyResampledTo($src, $dest, $destX) {
        $resized = imagecreatetruecolor(600, 600);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $src, 0, 0, 0, 0, 600, 600, imagesx($src), imagesy($src));
        imagecopy($dest, $resized, $destX, 0, 0, 0, 600, 600);
        imagedestroy($resized);
    }

    private function centerText($image, $text, $size, $y, $color) {
        $bbox = imagettfbbox($size, 0, $this->fontPath, $text);
        $textWidth = $bbox[2] - $bbox[0];
        $x = (int)((imagesx($image) - $textWidth) / 2);
        imagettftext($image, $size, 0, $x, $y, $color, $this->fontPath, $text);
    }

    private function centerMultilineTextIfLong($image, $text, $size, $startY, $lineHeight, $color, $maxChars = 26) {
        $lines = (mb_strlen($text) > $maxChars) ? explode("\n", wordwrap($text, $maxChars, "\n", true)) : [$text];
        $startY = $startY - 20 * (count($lines) - 1);
        foreach ($lines as $i => $line) {
            $this->centerText($image, $line, $size, (int)($startY + $i * $lineHeight), $color);
        }
    }
}