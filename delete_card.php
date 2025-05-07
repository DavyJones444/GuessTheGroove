<?php
require 'lib/db.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;
$id = $_GET['id'] ?? null;
if (!$userId || !$id) die("Zugriff verweigert.");

$stmt = $pdo->prepare("SELECT image_text, image_qr FROM cards WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);
$card = $stmt->fetch();
if ($card) {
    unlink("cards/images/" . $card['image_text']);
    unlink("cards/images/" . $card['image_qr']);
    $del = $pdo->prepare("DELETE FROM cards WHERE id = ? AND user_id = ?");
    $del->execute([$id, $userId]);
}
header("Location: profile.php");
exit;
