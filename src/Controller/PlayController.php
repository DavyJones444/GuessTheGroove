<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../Model/CardRepository.php';
require_once __DIR__ . '/../Service/UrlAnalyzer.php';

class PlayController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function handleRequest() {
        session_start();
        $repo = new CardRepository($this->pdo);
        
        $songUrl = $_GET['url'] ?? '';

        // 1. Check ID (via QR Code /123)
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $foundUrl = $repo->findSongLinkById($_GET['id']);
            if ($foundUrl) {
                $songUrl = $foundUrl;
            } else {
                // Optional: Error message setzten
            }
        }

        // 2. Check Hitster
        if (UrlAnalyzer::isHitster($songUrl)) {
            $details = UrlAnalyzer::getHitsterDetails($songUrl);
            $hitsterId = htmlspecialchars($details['id']);
            $hitsterLang = htmlspecialchars($details['lang']);
            
            // Lade spezielles Template und beende
            require __DIR__ . '/../../templates/pages/hitster.view.php';
            return; 
        }

        // 3. Normaler Player
        $service = UrlAnalyzer::detectService($songUrl);
        $token = $_SESSION['spotify_token'] ?? null;
        
        // Daten für die View vorbereiten
        $viewData = [
            'songUrl' => $songUrl,
            'service' => $service,
            'token' => $token
        ];

        // View laden
        require __DIR__ . '/../../templates/pages/play.view.php';
    }
}