<?php
// src/Service/PDFService.php

// Da FPDF oft global geladen wird via Composer, müssen wir evtl. use FPDF; nutzen
// oder \FPDF im Code schreiben.

class PDFService {
    
    private $uploadsDir;

    public function __construct() {
        // Pfad zu den Bildern
        $this->uploadsDir = __DIR__ . '/../../public/uploads/cards/';
    }

    public function generatePlaylistPdf($playlistName, $cards) {
        // FPDF initialisieren (via Composer Autoloader verfügbar)
        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(false);

        // Layout-Konstanten
        $cardWidth = 100; $cardHeight = 50;
        $cardsPerRow = 2; $cardsPerColumn = 5;
        $marginX = 5; $marginY = 5;
        
        $currentRow = 0; $currentColumn = 0;

        foreach ($cards as $card) {
            $textPath = $this->uploadsDir . $card['image_text'];
            $qrPath = $this->uploadsDir . $card['image_qr'];

            if (!file_exists($textPath) || !file_exists($qrPath)) {
                continue;
            }

            // Temporäres Bild erstellen (Text + QR nebeneinander)
            $tempFile = $this->createCompositeImage($textPath, $qrPath);

            // Auf PDF platzieren
            $x = $marginX + $currentColumn * ($cardWidth);
            $y = $marginY + $currentRow * ($cardHeight);
            $pdf->Image($tempFile, $x, $y, $cardWidth, $cardHeight);

            // Aufräumen
            unlink($tempFile);

            // Raster Logik
            $currentColumn++;
            if ($currentColumn >= $cardsPerRow) {
                $currentColumn = 0;
                $currentRow++;
            }

            if ($currentRow >= $cardsPerColumn) {
                $pdf->AddPage();
                $currentRow = 0; $currentColumn = 0;
            }
        }

        // PDF direkt an Browser senden
        $filename = "playlist_" . preg_replace('/[^a-z0-9]/i', '_', $playlistName) . ".pdf";
        $pdf->Output('D', $filename);
        exit;
    }

    private function createCompositeImage($textPath, $qrPath) {
        $combinedImage = imagecreatetruecolor(1200, 600);
        imagesavealpha($combinedImage, true);
        $transparent = imagecolorallocatealpha($combinedImage, 0, 0, 0, 127);
        imagefill($combinedImage, 0, 0, $transparent);

        $textImage = imagecreatefrompng($textPath);
        $qrImage = imagecreatefrompng($qrPath);

        imagecopyresampled($combinedImage, $textImage, 0, 0, 0, 0, 600, 600, imagesx($textImage), imagesy($textImage));
        imagecopyresampled($combinedImage, $qrImage, 600, 0, 0, 0, 600, 600, imagesx($qrImage), imagesy($qrImage));

        $tempFile = tempnam(sys_get_temp_dir(), 'card_') . '.png';
        imagepng($combinedImage, $tempFile);

        imagedestroy($textImage);
        imagedestroy($qrImage);
        imagedestroy($combinedImage);

        return $tempFile;
    }
}