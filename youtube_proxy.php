<?php
header('Content-Type: application/json');

$apiKey = 'AIzaSyDjdpeaTfz2oEryx4d9sZnSkVMq-BRlolM';
$videoId = $_GET['id'] ?? '';

if (!$videoId || !preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige oder fehlende Video-ID']);
    exit;
}

$url = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id=$videoId&key=$apiKey";

$response = file_get_contents($url);
if (!$response) {
    http_response_code(500);
    echo json_encode(['error' => 'Fehler beim Abrufen der Daten']);
    exit;
}

$data = json_decode($response, true);

if (empty($data['items'][0]['snippet'])) {
    echo json_encode(['error' => 'Keine Daten gefunden']);
    exit;
}

$snippet = $data['items'][0]['snippet'];

echo json_encode([
    'title' => $snippet['title'],
    'artist' => $snippet['channelTitle'], // YouTube-Kanalname
    'year' => substr($snippet['publishedAt'], 0, 4),
]);
