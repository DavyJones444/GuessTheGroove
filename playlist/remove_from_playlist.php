<?php
require '../lib/db.php';
session_start();

$playlistId = $_GET['playlist_id'] ?? null;
$cardId = $_GET['card_id'] ?? null;

if (!$playlistId || !$cardId) {
    die("Fehlende Parameter.");
}

// Nur der Besitzer darf löschen
$stmt = $pdo->prepare("SELECT user_id FROM playlists WHERE id = ?");
$stmt->execute([$playlistId]);
$playlist = $stmt->fetch();

if (!$playlist || $playlist['user_id'] != $_SESSION['user_id']) {
    die("Keine Berechtigung.");
}

// Verbindung aus Zwischentabelle entfernen
$stmt = $pdo->prepare("DELETE FROM playlist_cards WHERE playlist_id = ? AND card_id = ?");
$stmt->execute([$playlistId, $cardId]);

header("Location: playlist_detail.php?id=" . $playlistId);
exit;
?>
