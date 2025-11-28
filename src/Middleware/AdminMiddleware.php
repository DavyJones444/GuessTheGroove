<?php
// src/Middleware/AdminMiddleware.php

require_once __DIR__ . '/AuthMiddleware.php';

class AdminMiddleware {
    
    // Die IDs der erlaubten Admins (aus deiner admin_tool.php übernommen)
    private const ADMIN_IDS = [1, 2, 8];

    public static function protect() {
        // 1. Erstmal muss man überhaupt eingeloggt sein
        AuthMiddleware::protect();

        // 2. Prüfen, ob die ID berechtigt ist
        if (!in_array($_SESSION['user_id'], self::ADMIN_IDS)) {
            http_response_code(403);
            die("<h1>Zugriff verweigert</h1><p>Du hast keine Berechtigung für diesen Bereich.</p>");
        }
    }
}