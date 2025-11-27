<?php
// public/index.php

// Autoloader & Config laden (Pfade anpassen!)
require_once __DIR__ . '/../config/db.php';
// require_once __DIR__ . '/../vendor/autoload.php'; // Wenn du Composer hast

// Wir holen uns den Pfad, den der Nutzer eingegeben hat
// Wenn er "deine-seite.de/public/login" aufruft, steht hier "/login"
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Bereinige den Pfad vom "public" Prefix, falls XAMPP das nicht automatisch macht
$basePath = '/public'; 
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// ROUTING LOGIK
// Hier definierst du, was bei welcher URL passiert

switch ($path) {
    // ----------------------------------------------------
    // 1. Die Startseite
    // ----------------------------------------------------
    case '/':
    case '/index.php':
        require __DIR__ . '/../src/Controller/HomeController.php';
        // (new HomeController())->index();
        break;

    // ----------------------------------------------------
    // 2. Der Player
    // ----------------------------------------------------
    case '/play':
        require_once __DIR__ . '/../src/Controller/PlayController.php';
        $controller = new PlayController($pdo);
        $controller->handleRequest();
        break;

    // ----------------------------------------------------
    // 3. Login & User (Zukünftige Beispiele)
    // ----------------------------------------------------
    case '/login':
        require __DIR__ . '/../templates/pages/login.view.php';
        break;
    
    case '/profile':
        // CheckLoginMiddleware::verify();
        require __DIR__ . '/../templates/pages/profile.view.php';
        break;

    // ----------------------------------------------------
    // 4. Spezialfall: QR-Code Kurz-URLs (/123)
    // ----------------------------------------------------
    default:
        // Prüfen, ob der Pfad nur aus Zahlen besteht (z.B. "/123")
        if (preg_match('/^\/(\d+)$/', $path, $matches)) {
            // Wir "simulieren" den Parameter für den Controller
            $_GET['id'] = $matches[1];
            
            require_once __DIR__ . '/../src/Controller/PlayController.php';
            $controller = new PlayController($pdo);
            $controller->handleRequest();
        } else {
            // ------------------------------------------------
            // 5. 404 Fehlerseite (für alles Unbekannte)
            // ------------------------------------------------
            http_response_code(404);
            echo "404 - Seite nicht gefunden. <br> Du hast versucht auf '$path' zuzugreifen.";
            // require __DIR__ . '/../templates/pages/404.php';
        }
        break;
}