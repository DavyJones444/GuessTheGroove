<?php
require '../lib/db.php';
session_start();

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
$playlistId = $input['playlist_id'] ?? null;
$newName = trim($input['new_name'] ?? '');

if (!$playlistId || !$newName) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Daten.']);
    exit;
}

$stmt = $pdo->prepare("SELECT user_id FROM playlists WHERE id = ?");
$stmt->execute([$playlistId]);
$playlist = $stmt->fetch();

if (!$playlist || $playlist['user_id'] != ($_SESSION['user_id'] ?? null)) {
    echo json_encode(['success' => false, 'message' => 'Keine Berechtigung.']);
    exit;
}

$stmt = $pdo->prepare("UPDATE playlists SET name = ? WHERE id = ?");
$stmt->execute([$newName, $playlistId]);

echo json_encode(['success' => true]);
