<?php
require 'lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    $_SESSION['message'] = "Bitte fülle alle Felder aus.";
    header("Location: profile.php");
exit;
}

if ($newPassword !== $confirmPassword) {
    $_SESSION['message'] = "Die neuen Passwörter stimmen nicht überein.";
    header("Location: profile.php");
    exit;
}

/*
if (strlen($newPassword) < 6) {
    die("Das neue Passwort muss mindestens 6 Zeichen lang sein.");
}
*/

// Altes Passwort prüfen
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!password_verify($currentPassword, $user['password'])) {
    $_SESSION['message'] = "Das aktuelle Passwort ist falsch!";
    header("Location: profile.php");
}

// Neues Passwort speichern
$newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->execute([$newPasswordHash, $userId]);

// Erfolgreiche Rückmeldung und Weiterleitung
$_SESSION['message'] = "Passwort erfolgreich geändert.";
header("Location: profile.php");
exit;
