<?php
require '../lib/db.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;
$id = $_GET['id'] ?? null;
if (!$userId || !$id) die("Zugriff verweigert.");

$stmt = $pdo->prepare("SELECT image_text, image_qr FROM cards WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);
$card = $stmt->fetch();

if ($card) {
    $imageBasePath = realpath(__DIR__ . '/../card/images/');
    $imageTextPath = $imageBasePath . '/' . $card['image_text'];
    $imageQrPath = $imageBasePath . '/' . $card['image_qr'];

    if (is_file($imageTextPath)) {
        if (!unlink($imageTextPath)) error_log("Fehler beim Löschen von $imageTextPath");
    }
    if (is_file($imageQrPath)) {
        if (!unlink($imageQrPath)) error_log("Fehler beim Löschen von $imageQrPath");
    }

    $del = $pdo->prepare("DELETE FROM cards WHERE id = ? AND user_id = ?");
    $del->execute([$id, $userId]);
}

header("Location: ../profile.php");
exit;
