<?php
// src/Controller/AuthController.php

require_once __DIR__ . '/BaseController.php';

class AuthController extends BaseController {
    
    // ... login(), register() methoden ...

    public function verifyEmail() {
        $token = $_GET['token'] ?? null;

        if (!$token) {
            die("Kein Token angegeben."); // Oder schöne Fehlerseite
        }

        // Token suchen
        $stmt = $this->pdo->prepare("SELECT * FROM email_verifications WHERE token = ?");
        $stmt->execute([$token]);
        $verification = $stmt->fetch();

        if ($verification) {
            // User aktivieren
            $userId = $verification['user_id'];
            $stmt = $this->pdo->prepare("UPDATE users SET verified = 1 WHERE id = ?");
            $stmt->execute([$userId]);

            // Token löschen
            $stmt = $this->pdo->prepare("DELETE FROM email_verifications WHERE token = ?");
            $stmt->execute([$token]);

            // Redirect zur Startseite mit Erfolgsmeldung
            header("Location: /?verified=1");
            exit();
        } else {
            // Fehler anzeigen (Du könntest hier auch eine View rendern)
            die("Ungültiger oder abgelaufener Verifizierungstoken.");
        }
    }
}