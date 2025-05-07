<?php
$client_id = '33dfdad9829b4afe88eb33c85cc08f08';
$redirect_uri = 'https://localhost/callback';
$scope = 'streaming user-read-email user-read-private user-modify-playback-state user-read-playback-state';
$urlParam = $_GET['url'] ?? '';

$url = 'https://accounts.spotify.com/authorize?' . http_build_query([
    'response_type' => 'code',
    'client_id' => $client_id,
    'scope' => $scope,
    'redirect_uri' => $redirect_uri,
    'state' => urlencode($urlParam),
]);

header('Location: ' . $url);
exit;
