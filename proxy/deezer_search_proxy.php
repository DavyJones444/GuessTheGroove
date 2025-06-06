<?php
// Deezer API URL für die Suche
if (!isset($_GET['q'])) {
    http_response_code(400);
    echo json_encode(["error" => "Suchbegriff fehlt"]);
    exit;
}

$searchQuery = $_GET['q'];

// Deezer API URL für die Suche
$deezerApiUrl = "https://api.deezer.com/search?q=" . urlencode($searchQuery);

// Deezer-Daten abrufen
$searchData = file_get_contents($deezerApiUrl);

// Fehlerbehandlung, falls der Abruf fehlschlägt
if ($searchData === false) {
    http_response_code(500);
    echo json_encode(["error" => "Fehler beim Abrufen der Deezer-Daten."]);
    exit;
}

// Antwort als JSON zurückgeben
header('Content-Type: application/json');
echo $searchData;
?>
