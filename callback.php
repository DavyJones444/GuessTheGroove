<?php
session_start();
$client_id = '33dfdad9829b4afe88eb33c85cc08f08';
$client_secret = 'ba02214b896a46dcb5cd52b216a1903a';
$redirect_uri = 'https://localhost/callback';
$code = $_GET['code'];
$originalUrl = $_GET['state'] ?? '';

$response = file_get_contents('https://accounts.spotify.com/api/token', false, stream_context_create([
    'https' => [
        'method' => 'POST',
        'header' => [
            'Authorization: Basic ' . base64_encode("$client_id:$client_secret"),
            'Content-Type: application/x-www-form-urlencoded'
        ],
        'content' => http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirect_uri,
        ])
    ]
]));

$data = json_decode($response, true);
$_SESSION['spotify_token'] = $data['access_token'];

header("Location: player.php?url=" . urlencode($originalUrl));
exit;
