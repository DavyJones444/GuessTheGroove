<?php
require 'lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Du musst eingeloggt sein, um diese Aktion durchzuführen.']);
    exit();
}

$cardId = $_POST['card_id'] ?? null;
$isPublic = $_POST['is_public'] ?? null;

if ($cardId && ($isPublic == 0 || $isPublic == 1)) {
    // Status aktualisieren
    $stmt = $pdo->prepare("UPDATE cards SET is_public = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$isPublic, $cardId, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Ungültige Eingabe.']);
    exit();
}
