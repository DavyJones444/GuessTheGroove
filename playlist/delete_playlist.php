<?php
chdir(__DIR__ . '/..');
require 'lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../welcome");
    exit();
}

$playlistId = $_GET['id'] ?? null;
if (!$playlistId) die("Keine Playlist-ID übergeben.");

// Nur löschen, wenn die Playlist dem Nutzer gehört
$stmt = $pdo->prepare("DELETE FROM playlists WHERE id = ? AND user_id = ?");
$stmt->execute([$playlistId, $_SESSION['user_id']]);

// Zugehörige Einträge in playlist_cards löschen
$stmt = $pdo->prepare("DELETE FROM playlist_cards WHERE playlist_id = ?");
$stmt->execute([$playlistId]);

$_SESSION['message'] = "Playlist gelöscht.";
header("Location: ../profile?id=" . $_SESSION['user_id']);
exit();
