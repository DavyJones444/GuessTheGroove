<?php
// src/Service/YouTubeService.php

class YouTubeService {
    private $apiKey;
    private $ytDlpPath;

    public function __construct() {
        $this->apiKey = $_ENV['YT_API'];
        // Pfad zur exe (im bin Ordner)
        $this->ytDlpPath = __DIR__ . '/../../bin/yt-dlp.exe';
    }

    public function getVideoInfo($videoId) {
        if (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) return null;

        $url = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id=$videoId&key={$this->apiKey}";
        $response = @file_get_contents($url);
        
        if ($response) {
            $data = json_decode($response, true);
            if (!empty($data['items'][0]['snippet'])) {
                $snippet = $data['items'][0]['snippet'];
                return [
                    'title' => $snippet['title'],
                    'artist' => $snippet['channelTitle'],
                    'year' => substr($snippet['publishedAt'], 0, 4),
                ];
            }
        }
        return null;
    }

    public function streamAudio($url) {
        // ... (Dein yt-dlp Code aus youtube_audio_proxy.php) ...
        // Achte darauf, header() Aufrufe hier NICHT zu machen, das macht der Controller!
        // Stattdessen gibst du den Pfad zur temporären Datei zurück.
        
        $tmpFile = tempnam(sys_get_temp_dir(), 'yt_') . '.m4a';
        $cmd = escapeshellarg($this->ytDlpPath) . " -f bestaudio[ext=m4a] --max-filesize 10M -o \"$tmpFile\" -x --audio-format m4a --quiet " . escapeshellarg($url);
        
        exec($cmd, $output, $returnVar);

        if ($returnVar === 0 && file_exists($tmpFile)) {
            return $tmpFile;
        }
        return false;
    }
}