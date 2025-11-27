<?php

class BaseController {
    protected $pdo;
    protected $currentUser = null;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        
        // Session starten, falls noch nicht passiert
        if (session_status() === PHP_SESSION_NONE) {
            // Session-Cookie Parameter setzen, damit sie überall gelten
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/', // WICHTIG: Gilt für die ganze Domain
                'domain' => '', 
                'secure' => false, // Bei localhost false
                'httponly' => true
            ]);
            session_start();
        }

        // Automatisch prüfen, ob User eingeloggt ist
        $this->loadCurrentUser();
    }

    private function loadCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->pdo->prepare("SELECT id, name, profile_pic FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $this->currentUser = $user;
            }
        }
    }

    // Hilfsmethode um Views zu laden
    protected function render($viewPath, $data = []) {
        // Wir fügen den User IMMER zu den Daten hinzu, damit header.php ihn kennt
        $data['user'] = $this->currentUser;
        
        // $viewData ist der Name der Variable, die in den Templates verfügbar ist
        $viewData = $data; 

        // View laden
        require __DIR__ . '/../../templates/' . $viewPath;
    }
}