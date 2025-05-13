<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Playlist-ID fehlt"]);
    exit;
}

$playlistId = $_GET['id'];
$url = "https://api.deezer.com/playlist/$playlistId";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(["error" => "cURL Fehler: " . curl_error($ch)]);
    exit;
}
curl_close($ch);

http_response_code($httpcode);
echo $response;
