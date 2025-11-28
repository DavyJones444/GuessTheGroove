<?php
// src/Controller/ApiController.php

require_once __DIR__ . '/../Service/SpotifyService.php';
require_once __DIR__ . '/../Service/DeezerService.php';
require_once __DIR__ . '/../Service/YouTubeService.php';

class ApiController {
    
    // Deezer Track
    public function deezerTrack() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        if (!$id) { echo json_encode(['error' => 'ID fehlt']); exit; }
        
        echo (new DeezerService())->getTrack($id);
    }

    // Deezer Search
    public function deezerSearch() {
        header('Content-Type: application/json');
        $q = $_GET['q'] ?? '';
        if (!$q) { echo json_encode(['error' => 'Query fehlt']); exit; }

        echo (new DeezerService())->search($q);
    }

    // Spotify Track
    public function spotifyTrack() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        if (!$id) { echo json_encode(['error' => 'ID fehlt']); exit; }

        try {
            echo (new SpotifyService())->getTrack($id);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Spotify Playlist
    public function spotifyPlaylist() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        if (!$id) { echo json_encode(['error' => 'ID fehlt']); exit; }

        try {
            echo (new SpotifyService())->getPlaylist($id);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // YouTube Info
    public function youtubeInfo() {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        if (!$id) { echo json_encode(['error' => 'ID fehlt']); exit; }

        $info = (new YouTubeService())->getVideoInfo($id);
        if ($info) echo json_encode($info);
        else echo json_encode(['error' => 'Nicht gefunden']);
    }

    // YouTube Audio Stream (Proxy)
    public function youtubeAudio() {
        $url = $_GET['url'] ?? null;
        if (!$url) { http_response_code(400); exit; }

        $file = (new YouTubeService())->streamAudio($url);
        
        if ($file) {
            header('Content-Type: audio/mp4');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            unlink($file); // Temp Datei löschen
            exit;
        }
        http_response_code(500);
        echo "Fehler";
    }
}