<?php
// src/Middleware/AuthMiddleware.php

class AuthMiddleware {
    
    // Prüft, ob User eingeloggt ist. Wenn nicht -> Redirect
    public static function protect() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }
    }

    // Optional: Prüft nur, ob eingeloggt (gibt true/false zurück)
    public static function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
}