<?php
require 'lib/db.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) die("Nicht eingeloggt.");

// Kartenbilder löschen
$stmt = $pdo->prepare("SELECT image_text, image_qr FROM cards WHERE user_id = ?");
$stmt->execute([$userId]);
foreach ($stmt->fetchAll() as $card) {
    if (!empty($card['image_text']) && file_exists("cards/images/" . $card['image_text'])) {
        unlink("cards/images/" . $card['image_text']);
    }
    if (!empty($card['image_qr']) && file_exists("cards/images/" . $card['image_qr'])) {
        unlink("cards/images/" . $card['image_qr']);
    }
}
// Karten aus Datenbank löschen
$pdo->prepare("DELETE FROM cards WHERE user_id = ?")->execute([$userId]);

// Profilbild löschen (falls nicht Standard)
$stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
$stmt->execute([$userId]);
$profilePic = $stmt->fetchColumn();
if ($profilePic && $profilePic !== 'default_profile.png' && file_exists("uploads/" . $profilePic)) {
    unlink("uploads/" . $profilePic);
}

// Verifizierungseintrag löschen
$pdo->prepare("DELETE FROM email_verifications WHERE user_id = ?")->execute([$userId]);

// Falls Passwort-Zurücksetzen verwendet wurde: Code löschen
$pdo->prepare("DELETE FROM login_codes WHERE user_id = ?")->execute([$userId]);

// Benutzer löschen
$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);

// Session beenden
session_destroy();
header("Location: index.php");
exit;
