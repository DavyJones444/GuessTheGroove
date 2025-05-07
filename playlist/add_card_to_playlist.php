<?php
chdir(__DIR__ . '/..');
require 'lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome");
    exit();
}

$cardId = $_POST['card_id'] ?? null;
$playlistId = $_POST['playlist_id'] ?? null;

if (!$cardId || !$playlistId) {
    $_SESSION['message'] = "Ungültige Eingabe.";
    header("Location: ../profile?id=" . $_SESSION['user_id']);
    exit();
}

// Prüfen, ob die Playlist dem Nutzer gehört
$stmt = $pdo->prepare("SELECT * FROM playlists WHERE id = ? AND user_id = ?");
$stmt->execute([$playlistId, $_SESSION['user_id']]);
$playlist = $stmt->fetch();

if (!$playlist) {
    $_SESSION['message'] = "Playlist nicht gefunden oder keine Berechtigung.";
    header("Location: ../profile?id=" . $_SESSION['user_id']);
    exit();
}

// Beziehung speichern (falls nicht schon vorhanden)
$stmt = $pdo->prepare("SELECT * FROM playlist_cards WHERE playlist_id = ? AND card_id = ?");
$stmt->execute([$playlistId, $cardId]);
if (!$stmt->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO playlist_cards (playlist_id, card_id) VALUES (?, ?)");
    $stmt->execute([$playlistId, $cardId]);
}

$_SESSION['message'] = "Karte zur Playlist hinzugefügt.";
header("Location: ../profile?id=" . $_SESSION['user_id']);
exit();
