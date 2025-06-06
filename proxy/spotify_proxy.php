<?php

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$clientId = $_ENV['SPOTIFY_CLIENT_ID'];
$clientSecret = $_ENV['SPOTIFY_CLIENT_SECRET'];

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Track-ID fehlt"]);
    exit;
}

// Access-Token holen
$tokenUrl = 'https://accounts.spotify.com/api/token';
$auth = base64_encode("$clientId:$clientSecret");

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic $auth",
    "Content-Type: application/x-www-form-urlencoded"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (!isset($data['access_token'])) {
    echo json_encode(["error" => "Token konnte nicht abgerufen werden"]);
    exit;
}
$token = $data['access_token'];

// Songdaten abrufen
$trackId = $_GET['id'];
$ch = curl_init("https://api.spotify.com/v1/tracks/$trackId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
$trackData = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $trackData;
