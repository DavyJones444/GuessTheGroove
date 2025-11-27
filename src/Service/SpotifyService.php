<?php
// src/Service/SpotifyService.php

class SpotifyService {
    private $clientId;
    private $clientSecret;
    private $token;

    public function __construct() {
        $this->clientId = $_ENV['SPOTIFY_CLIENT_ID'];
        $this->clientSecret = $_ENV['SPOTIFY_CLIENT_SECRET'];
    }

    private function getAccessToken() {
        if ($this->token) return $this->token;

        $tokenUrl = 'https://accounts.spotify.com/api/token'; // URL korrigiert (war im Proxy falsch?)
        $auth = base64_encode("{$this->clientId}:{$this->clientSecret}");

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
        if (isset($data['access_token'])) {
            $this->token = $data['access_token'];
            return $this->token;
        }
        throw new Exception("Spotify Token Error");
    }

    public function getTrack($id) {
        $token = $this->getAccessToken();
        $url = "https://api.spotify.com/v1/tracks/$id"; // URL korrigiert
        return $this->makeRequest($url, $token);
    }

    public function getPlaylist($id) {
        $token = $this->getAccessToken();
        $url = "https://api.spotify.com/v1/playlists/$id"; // URL korrigiert
        return $this->makeRequest($url, $token);
    }

    private function makeRequest($url, $token) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response; // Gibt rohes JSON zurück
    }
}