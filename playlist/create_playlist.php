<?php
chdir(__DIR__ . '/..');
require 'lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome");
    exit();
}

$name = trim($_POST['name'] ?? '');
$cardId = $_POST['card_id'] ?? null;

if ($name === '') {
    $_SESSION['message'] = "Playlist-Name darf nicht leer sein.";
    header("Location: ../profile?id=" . $_SESSION['user_id']);
    exit();
}

// Neue Playlist erstellen
$stmt = $pdo->prepare("INSERT INTO playlists (user_id, name) VALUES (?, ?)");
$stmt->execute([$_SESSION['user_id'], $name]);
$newPlaylistId = $pdo->lastInsertId();

// Karte zu Playlist hinzufügen, falls mitgegeben
if ($cardId) {
    $stmt = $pdo->prepare("INSERT INTO playlist_cards (playlist_id, card_id) VALUES (?, ?)");
    $stmt->execute([$newPlaylistId, $cardId]);
}

$_SESSION['message'] = "Playlist erstellt.";
header("Location: ../profile?id=" . $_SESSION['user_id']);
exit();
