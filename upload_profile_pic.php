<?php
require 'lib/db.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    die("Nicht eingeloggt.");
}

if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
    die("Fehler beim Hochladen.");
}

$uploadDir = 'uploads/';
$ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
$allowed = ['jpg', 'jpeg', 'png', 'gif'];

if (!in_array(strtolower($ext), $allowed)) {
    die("Nur JPG, PNG oder GIF erlaubt.");
}

$filename = uniqid() . '.' . $ext;
$filepath = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filepath)) {
    die("Fehler beim Speichern der Datei.");
}

// Dateiname in Datenbank speichern
$stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
$stmt->execute([$filename, $userId]);

header("Location: profile.php");
exit;
