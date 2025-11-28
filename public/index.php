<?php
// public/index.php

// Ermittelt den Pfad zum Skript (z.B. "/public/")
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$scriptDir = str_replace('\\', '/', $scriptDir); // Windows Fix
$rootPath = rtrim($scriptDir, '/') . '/';

define('ROOT_URL', $rootPath);

require_once __DIR__ . '/../vendor/autoload.php';

// 3. .env Datei laden (WICHTIG!)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

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
    case '/welcome':
    require_once __DIR__ . '/../src/Controller/WelcomeController.php';
    $controller = new WelcomeController($pdo);
    $controller->index();
    break;

    case '/index.php':
    case '/home':
        require_once __DIR__ . '/../src/Controller/HomeController.php';
        (new HomeController($pdo))->index();
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
    
    case '/profile':
        require_once __DIR__ . '/../src/Controller/ProfileController.php';
        (new ProfileController($pdo))->index();
        break;

    case '/profile/upload': // Route für Bild-Upload
        require_once __DIR__ . '/../src/Controller/ProfileController.php';
        (new ProfileController($pdo))->uploadPicture();
        break;
    
    case '/impressum':
        require_once __DIR__ . '/../src/Controller/PageController.php';
        (new PageController($pdo))->impressum();
        break;

    case '/datenschutz':
        require_once __DIR__ . '/../src/Controller/PageController.php';
        (new PageController($pdo))->privacy();
        break;

    case '/kontakt':
        require_once __DIR__ . '/../src/Controller/PageController.php';
        (new PageController($pdo))->contact();
        break;
    
    case '/verify':
    require_once __DIR__ . '/../src/Controller/AuthController.php';
    (new AuthController($pdo))->verifyEmail();
    break;

    case '/cards/create':
        require_once __DIR__ . '/../src/Controller/CardController.php';
        (new CardController($pdo))->create();
        break;

    case '/cards/store':
        require_once __DIR__ . '/../src/Controller/CardController.php';
        (new CardController($pdo))->store();
        break;

    case '/cards/delete': // Aufruf meist via /cards/delete?id=123
        require_once __DIR__ . '/../src/Controller/CardController.php';
        (new CardController($pdo))->delete($_GET['id'] ?? 0);
        break;

    case '/cards/download':
        require_once __DIR__ . '/../src/Controller/CardController.php';
        (new CardController($pdo))->download($_GET['id'] ?? 0);
        break;

    case '/cards/status': // AJAX Endpoint
        require_once __DIR__ . '/../src/Controller/CardController.php';
        (new CardController($pdo))->updateStatus();
        break;

    case '/playlists/show': // ?id=123
        require_once __DIR__ . '/../src/Controller/PlaylistController.php';
        (new PlaylistController($pdo))->show($_GET['id'] ?? 0);
        break;

    case '/playlists/create':
        require_once __DIR__ . '/../src/Controller/PlaylistController.php';
        (new PlaylistController($pdo))->create();
        break;

    case '/playlists/delete':
        require_once __DIR__ . '/../src/Controller/PlaylistController.php';
        (new PlaylistController($pdo))->delete($_GET['id'] ?? 0);
        break;

    case '/playlists/update_name': // JSON POST
        require_once __DIR__ . '/../src/Controller/PlaylistController.php';
        (new PlaylistController($pdo))->updateName();
        break;

    case '/playlists/add_card':
        require_once __DIR__ . '/../src/Controller/PlaylistController.php';
        (new PlaylistController($pdo))->addCard();
        break;

    case '/playlists/remove_card':
        require_once __DIR__ . '/../src/Controller/PlaylistController.php';
        (new PlaylistController($pdo))->removeCard();
        break;

    case '/playlists/export':
        require_once __DIR__ . '/../src/Controller/PlaylistController.php';
        (new PlaylistController($pdo))->export($_GET['id'] ?? 0);
        break;

    // API Routen (Proxies)
    case '/api/deezer/track':
        require_once __DIR__ . '/../src/Controller/ApiController.php';
        (new ApiController())->deezerTrack();
        break;

    case '/api/deezer/search':
        require_once __DIR__ . '/../src/Controller/ApiController.php';
        (new ApiController())->deezerSearch();
        break;

    case '/api/spotify/track':
        require_once __DIR__ . '/../src/Controller/ApiController.php';
        (new ApiController())->spotifyTrack();
        break;

    case '/api/spotify/playlist':
        require_once __DIR__ . '/../src/Controller/ApiController.php';
        (new ApiController())->spotifyPlaylist();
        break;

    case '/api/youtube/info':
        require_once __DIR__ . '/../src/Controller/ApiController.php';
        (new ApiController())->youtubeInfo();
        break;

    case '/api/youtube/audio':
        require_once __DIR__ . '/../src/Controller/ApiController.php';
        (new ApiController())->youtubeAudio();
        break;
    
    case '/login':
        require_once __DIR__ . '/../src/Controller/AuthController.php';
        (new AuthController($pdo))->login();
        break;

    case '/login/code':
        require_once __DIR__ . '/../src/Controller/AuthController.php';
        (new AuthController($pdo))->loginWithCode();
        break;

    case '/logout':
        require_once __DIR__ . '/../src/Controller/AuthController.php';
        (new AuthController($pdo))->logout();
        break;

    case '/register':
        require_once __DIR__ . '/../src/Controller/AuthController.php';
        (new AuthController($pdo))->register();
        break;

    case '/forgot-password':
        require_once __DIR__ . '/../src/Controller/AuthController.php';
        (new AuthController($pdo))->forgotPassword();
        break;

    case '/reset-password':
        require_once __DIR__ . '/../src/Controller/AuthController.php';
        (new AuthController($pdo))->resetPassword();
        break;

    // ACCOUNT SETTINGS (POST Requests, leiten zurück zum Profil)
    case '/account/password':
        require_once __DIR__ . '/../src/Controller/ProfileController.php';
        (new ProfileController($pdo))->updatePassword();
        break;

    case '/account/username':
        require_once __DIR__ . '/../src/Controller/ProfileController.php';
        (new ProfileController($pdo))->updateUsername();
        break;

    case '/account/delete':
        require_once __DIR__ . '/../src/Controller/ProfileController.php';
        (new ProfileController($pdo))->deleteAccount();
        break;

    case '/account/email':
        require_once __DIR__ . '/../src/Controller/ProfileController.php';
        (new ProfileController($pdo))->updateEmail();
        break;
        
    case '/cards/edit':
        require_once __DIR__ . '/../src/Controller/CardController.php';
        (new CardController($pdo))->edit($_GET['id'] ?? 0);
        break;

    case '/cards/update':
        require_once __DIR__ . '/../src/Controller/CardController.php';
        (new CardController($pdo))->update($_GET['id'] ?? 0);
        break;
    
    // --- DEV: E-Mail Vorschau ---
    case '/email-preview':
        // Test-Daten simulieren
        $subject = 'Vorschau: E-Mail Bestätigung';
        $headline = 'Willkommen bei Guess The Groove!';
        $name = 'Davy';
        $content = "Hallo $name,\n\nschön, dass du dabei bist! Das ist eine Vorschau deiner HTML-E-Mail.\nWir nutzen Tabellen-Layouts, damit es auch in Outlook gut aussieht.";
        
        $buttonUrl = '/verify?token=test123456';
        $buttonText = 'Jetzt bestätigen';
        
        // Mock für Base URL (falls .env nicht geladen wäre, hier zur Sicherheit)
        $_ENV['APP_URL'] = $_ENV['APP_URL'] ?? 'http://localhost/GuessTheGroove/public';

        // Template laden und direkt anzeigen
        require __DIR__ . '/../templates/mail/default.php';
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