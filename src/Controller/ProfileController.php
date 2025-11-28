<?php
// src/Controller/ProfileController.php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Model/CardRepository.php';
require_once __DIR__ . '/../Model/PlaylistRepository.php';
require_once __DIR__ . '/../Model/UserRepository.php';
require_once __DIR__ . '/../Service/QRCodeService.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class ProfileController extends BaseController {
    
    public function index() {
        AuthMiddleware::protect();
        $userId = $_SESSION['user_id'];
        $profileId = $_GET['id'] ?? $userId; // Profil eines anderen Users ansehen?

        $cardRepo = new CardRepository($this->pdo);
        $playlistRepo = new PlaylistRepository($this->pdo);
        $userRepo = new UserRepository($this->pdo);

        // User laden (Entweder eigener oder fremder)
        $profileUser = $userRepo->findById($profileId);
        if (!$profileUser) {
            die("Benutzer nicht gefunden.");
        }

        // Karten & Playlists holen
        $cards = $cardRepo->findAllByUserId($profileId);
        
        // Playlists nur anzeigen, wenn man sein eigenes Profil ansieht 
        // (oder du passt die Logik an, wenn Playlists öffentlich sein sollen)
        $playlists = ($userId == $profileId) ? $playlistRepo->findAllByUserId($userId) : [];

        $this->render('pages/profile.view.php', [
            'title' => "Profil von " . $profileUser['name'],
            'profileUser' => $profileUser, // Der User, dessen Profil wir sehen
            'cards' => $cards,
            'playlists' => $playlists,
            'isOwner' => ($userId == $profileId),
            'batchSuccess' => isset($_GET['batch']) && $_GET['batch'] == 1,
            'message' => $_SESSION['message'] ?? null
        ]);
        
        // Flash Message löschen
        unset($_SESSION['message']);
    }

    public function uploadPicture() {
        AuthMiddleware::protect();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
            $file = $_FILES['profile_pic'];
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed) && $file['size'] < 5000000) {
                $newName = uniqid('profile_', true) . '.' . $ext;
                $target = __DIR__ . '/../../public/uploads/' . $newName;
                
                if (move_uploaded_file($file['tmp_name'], $target)) {
                    // DB Update
                    $stmt = $this->pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                    $stmt->execute([$newName, $_SESSION['user_id']]);
                }
            }
        }
        header("Location: /profile");
        exit;
    }

    public function updatePassword() {
        AuthMiddleware::protect();
        $userRepo = new UserRepository($this->pdo);
        $userId = $_SESSION['user_id'];
        
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = $userRepo->findById($userId);

        if (password_verify($current, $user['password'])) {
            if ($new === $confirm && !empty($new)) {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $userRepo->updatePassword($userId, $hash);
                $_SESSION['message'] = "Passwort erfolgreich geändert.";
            } else {
                $_SESSION['message'] = "Die neuen Passwörter stimmen nicht überein.";
            }
        } else {
            $_SESSION['message'] = "Das aktuelle Passwort ist falsch.";
        }
        header("Location: /profile");
        exit;
    }

    public function updateUsername() {
        AuthMiddleware::protect();
        $newName = trim($_POST['new_name'] ?? '');
        if ($newName) {
            (new UserRepository($this->pdo))->updateName($_SESSION['user_id'], $newName);
            $_SESSION['message'] = "Benutzername geändert.";
        }
        header("Location: /profile");
        exit;
    }

    public function deleteAccount() {
        AuthMiddleware::protect();
        $userId = $_SESSION['user_id'];
        
        // 1. Bilder löschen
        $cardRepo = new CardRepository($this->pdo);
        $cards = $cardRepo->findAllByUserId($userId);
        $qrService = new QRCodeService();
        
        foreach ($cards as $card) {
            $qrService->deleteImages($card['image_text'], $card['image_qr']);
        }

        // 2. Profilbild löschen
        $userRepo = new UserRepository($this->pdo);
        $user = $userRepo->findById($userId);
        if ($user['profile_pic'] && $user['profile_pic'] !== 'default_profile.png') {
            $path = __DIR__ . '/../../public/uploads/' . $user['profile_pic'];
            if (file_exists($path)) unlink($path);
        }

        // 3. User löschen
        $userRepo->deleteUser($userId);

        session_destroy();
        header("Location: /");
        exit;
    }

    public function updateEmail() {
        AuthMiddleware::protect();
        $newEmail = trim($_POST['new_email'] ?? '');
        
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['message'] = "Ungültige E-Mail-Adresse.";
        } else {
            $userRepo = new UserRepository($this->pdo);
            if ($userRepo->updateEmail($_SESSION['user_id'], $newEmail)) {
                $_SESSION['message'] = "E-Mail erfolgreich geändert.";
            } else {
                $_SESSION['message'] = "Diese E-Mail wird bereits verwendet.";
            }
        }
        header("Location: /profile");
        exit;
    }
}