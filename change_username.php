<?php
require 'lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$newName = $_POST['new_name'] ?? '';

// Neuen Namen speichern
$stmt = $pdo->prepare(query: "UPDATE users SET name = ? WHERE id = ?");
$stmt->execute([$newName, $userId]);

// Erfolgreiche Rückmeldung und Weiterleitung
$_SESSION['message'] = "Benutzername erfolgreich geändert.";
header("Location: profile.php");
exit;
