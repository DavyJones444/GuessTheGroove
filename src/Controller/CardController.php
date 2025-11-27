<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Model/CardRepository.php';
require_once __DIR__ . '/../Service/QRCodeService.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class CardController extends BaseController {
    
    private $cardRepo;
    private $qrService;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->cardRepo = new CardRepository($pdo);
        $this->qrService = new QRCodeService();
    }

    // Zeigt das Formular an
    public function create() {
        AuthMiddleware::protect();
        $this->render('cards/create.view.php', ['title' => 'Karte erstellen']);
    }

    // Verarbeitet das Formular (Batch & Einzeln)
    public function store() {
        AuthMiddleware::protect();
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Fall A: Batch (Playlist)
            if (isset($_POST['batch']) && isset($_POST['tracks'])) {
                $firstTrack = $_POST['tracks'][0] ?? null;
                $playlistName = $firstTrack['title'] ?? 'Neue Playlist';
                
                $playlistId = $this->cardRepo->createPlaylist($userId, $playlistName);

                foreach ($_POST['tracks'] as $track) {
                    $cardId = $this->processSingleCard($userId, $track);
                    $this->cardRepo->addCardToPlaylist($playlistId, $cardId);
                }
                header("Location: /profile?batch=1");
                exit;
            } 
            
            // Fall B: Einzelne Karte
            else {
                $this->processSingleCard($userId, $_POST);
                header("Location: /profile");
                exit;
            }
        }
    }

    // Hilfsfunktion: Logik für EINE Karte (DB + Bilder)
    private function processSingleCard($userId, $data) {
        // 1. Plattform erkennen
        $songlink = $data['songlink'];
        $platform = 'Andere';
        if (strpos($songlink, 'deezer.com') !== false) $platform = 'Deezer';
        elseif (strpos($songlink, 'spotify.com') !== false) $platform = 'Spotify';
        elseif (strpos($songlink, 'youtu') !== false) $platform = 'YouTube';

        // 2. DB Eintrag erstellen (ohne Bilder)
        $cardId = $this->cardRepo->create($userId, [
            'title' => $data['title'],
            'artist' => $data['artist'],
            'year' => $data['year'],
            'songlink' => $songlink,
            'platform' => $platform
        ]);

        // 3. Bilder generieren
        // ACHTUNG: Hier nutzen wir den Router-Link für den QR Code
        // .env APP_URL wäre hier sauberer, aber hardcoded geht auch erstmal
        $qrLink = "https://gtg.luda-vision.de/" . $cardId;
        
        $images = $this->qrService->generateCardImages(
            $data['title'], 
            $data['artist'], 
            $data['year'], 
            $qrLink
        );

        // 4. DB Update mit Bildern
        $this->cardRepo->updateImages($cardId, $images['image_text'], $images['image_qr']);

        return $cardId;
    }

    // Löschen
    public function delete($id) {
        AuthMiddleware::protect();
        $userId = $_SESSION['user_id'];
        
        $card = $this->cardRepo->getCardByIdAndUser($id, $userId);
        
        if ($card) {
            // Bilder löschen
            $this->qrService->deleteImages($card['image_text'], $card['image_qr']);
            // DB Eintrag löschen
            $this->cardRepo->delete($id, $userId);
        }
        
        header("Location: /profile");
        exit;
    }

    // Download
    public function download($id) {
        // Hier kein Auth-Protect zwingend nötig, wenn public cards erlaubt sind?
        // Aber im Originalcode war requireLogin drin? Nein, nur db.php.
        // Wir lassen es mal offen oder schützen es:
        // AuthMiddleware::protect(); 

        // Prüfen ob Karte existiert (User ID egal, vielleicht will man fremde Karten laden?)
        // Im Original war keine User-Prüfung beim Download.
        // Wir nutzen eine einfache getById Methode (müsstest du im Repo noch ergänzen oder findSongLinkById anpassen)
        $stmt = $this->pdo->prepare("SELECT image_text, image_qr FROM cards WHERE id = ?");
        $stmt->execute([$id]);
        $card = $stmt->fetch();

        if ($card) {
            $img = $this->qrService->generateCompositeForDownload($card['image_text'], $card['image_qr']);
            if ($img) {
                header('Content-Type: image/png');
                header('Content-Disposition: attachment; filename="card_' . $id . '.png"');
                imagepng($img);
                imagedestroy($img);
                exit;
            }
        }
        die("Fehler beim Download.");
    }

    // AJAX Status Update
    public function updateStatus() {
        AuthMiddleware::protect();
        
        header('Content-Type: application/json');
        $cardId = $_POST['card_id'] ?? null;
        $isPublic = $_POST['is_public'] ?? null;

        if ($cardId && ($isPublic == 0 || $isPublic == 1)) {
            $this->cardRepo->updateStatus($cardId, $_SESSION['user_id'], $isPublic);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ungültige Eingabe']);
        }
        exit;
    }

    public function edit($id) {
        AuthMiddleware::protect();
        $userId = $_SESSION['user_id'];
        
        $card = $this->cardRepo->getCardByIdAndUser($id, $userId);
        
        if (!$card) {
            header("Location: /profile"); // Oder Fehlerseite
            exit;
        }

        $this->render('cards/edit.view.php', [
            'title' => 'Karte bearbeiten',
            'card' => $card
        ]);
    }

    public function update($id) {
        AuthMiddleware::protect();
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            // Plattform ermitteln (Logik in Helper auslagern wäre noch besser)
            $platform = 'Andere';
            if (strpos($data['songlink'], 'deezer.com') !== false) $platform = 'Deezer';
            elseif (strpos($data['songlink'], 'spotify') !== false) $platform = 'Spotify';
            elseif (strpos($data['songlink'], 'youtu') !== false) $platform = 'YouTube';
            
            $data['platform'] = $platform;

            // 1. Metadaten Update
            $this->cardRepo->update($id, $userId, $data);

            // 2. Bilder neu generieren (Optional: Nur wenn sich Daten geändert haben)
            // Alte löschen
            $card = $this->cardRepo->getCardByIdAndUser($id, $userId);
            $this->qrService->deleteImages($card['image_text'], $card['image_qr']);
            
            // Neue erstellen
            $qrLink = "https://gtg.luda-vision.de/" . $id;
            $images = $this->qrService->generateCardImages($data['title'], $data['artist'], $data['year'], $qrLink);
            $this->cardRepo->updateImages($id, $images['image_text'], $images['image_qr']);

            header("Location: /profile");
            exit;
        }
    }
}