<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../Model/CardRepository.php';
require_once __DIR__ . '/../Model/HitsterRepository.php';
require_once __DIR__ . '/../Service/UrlAnalyzer.php';

class PlayController extends BaseController{

    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    public function handleRequest() {
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

        // 2. Check Hitster (Original Karten)
        if (UrlAnalyzer::isHitster($songUrl)) {
            $details = UrlAnalyzer::getHitsterDetails($songUrl);
            $hitsterId = $details['id']; // z.B. "00253"

            // NEU: Prüfen ob wir ein Mapping in der Datenbank haben
            $hitsterRepo = new HitsterRepository($this->pdo);
            $mappedUrl = $hitsterRepo->findByHitsterId($hitsterId);

            if ($mappedUrl) {
                // TREFFER! Wir nutzen den gemappten Songlink
                // und lassen den Code weiterlaufen, als wäre es ein normaler Spotify/Deezer Link
                $songUrl = $mappedUrl;
            } else {
                // KEIN Treffer -> Original Hitster Verhalten (App Link anzeigen)
                $this->render('pages/hitster.view.php', [
                    'title' => 'Original Hitster Karte',
                    'hitsterId' => $hitsterId,
                    'originalUrl' => $songUrl // Die gescannte URL (z.B. hitstergame.com/...)
                ]);
                return; // Abbruch, da wir eine andere View zeigen
            }
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
        $this->render('pages/play.view.php', [
            'songUrl' => $songUrl,
            'service' => $service,
            'token' => $token,
            'title' => 'Spielen' // Titel für den Header
        ]);
    }
}