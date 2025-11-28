<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Model/PlaylistRepository.php';
require_once __DIR__ . '/../Service/PDFService.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class PlaylistController extends BaseController {
    
    private $playlistRepo;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->playlistRepo = new PlaylistRepository($pdo);
    }

    // Detailansicht
    public function show($id) {
        $playlist = $this->playlistRepo->findById($id);
        
        if (!$playlist) {
            // Error handling oder Redirect
            header("Location: /profile"); 
            exit;
        }

        $cards = $this->playlistRepo->getCardsInPlaylist($id);

        $this->render('playlist/detail.view.php', [
            'title' => "Playlist: " . htmlspecialchars($playlist['name']),
            'playlist' => $playlist,
            'cards' => $cards
        ]);
    }

    // Erstellen
    public function create() {
        AuthMiddleware::protect();
        $name = trim($_POST['name'] ?? '');
        $cardId = $_POST['card_id'] ?? null;

        if ($name) {
            $newId = $this->playlistRepo->create($_SESSION['user_id'], $name);
            if ($cardId) {
                $this->playlistRepo->addCard($newId, $cardId);
            }
        }
        header("Location: /profile");
        exit;
    }

    // Löschen
    public function delete($id) {
        AuthMiddleware::protect();
        $playlist = $this->playlistRepo->findById($id);

        if ($playlist && $playlist['user_id'] == $_SESSION['user_id']) {
            $this->playlistRepo->delete($id);
        }
        header("Location: /profile");
        exit;
    }

    // Karte hinzufügen
    public function addCard() {
        AuthMiddleware::protect();
        $cardId = $_POST['card_id'] ?? null;
        $playlistId = $_POST['playlist_id'] ?? null;

        if ($cardId && $playlistId) {
            $playlist = $this->playlistRepo->findById($playlistId);
            if ($playlist && $playlist['user_id'] == $_SESSION['user_id']) {
                $this->playlistRepo->addCard($playlistId, $cardId);
            }
        }
        header("Location: /profile");
        exit;
    }

    // Karte entfernen
    public function removeCard() {
        AuthMiddleware::protect();
        $cardId = $_GET['card_id'] ?? null;
        $playlistId = $_GET['playlist_id'] ?? null;

        if ($cardId && $playlistId) {
            $playlist = $this->playlistRepo->findById($playlistId);
            if ($playlist && $playlist['user_id'] == $_SESSION['user_id']) {
                $this->playlistRepo->removeCard($playlistId, $cardId);
            }
            header("Location: /playlists/show?id=" . $playlistId);
            exit;
        }
        header("Location: /profile");
    }

    // Namen ändern (AJAX)
    public function updateName() {
        AuthMiddleware::protect();
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents("php://input"), true);
        $id = $input['playlist_id'] ?? null;
        $newName = trim($input['new_name'] ?? '');

        if ($id && $newName) {
            $playlist = $this->playlistRepo->findById($id);
            if ($playlist && $playlist['user_id'] == $_SESSION['user_id']) {
                $this->playlistRepo->updateName($id, $newName);
                echo json_encode(['success' => true]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Fehler']);
        exit;
    }

    // PDF Export
    public function export($id) {
        // Optional: Auth Check, oder darf jeder exportieren?
        // AuthMiddleware::protect(); 
        
        $playlist = $this->playlistRepo->findById($id);
        if (!$playlist) die("Playlist nicht gefunden.");

        $cards = $this->playlistRepo->getCardsInPlaylist($id);
        
        $pdfService = new PDFService();
        $pdfService->generatePlaylistPdf($playlist['name'], $cards);
    }
}