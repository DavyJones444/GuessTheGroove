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
        // Temp Datei Pfad
        $tmpFile = tempnam(sys_get_temp_dir(), 'yt_') . '.m4a';
        
        // Pfad zur EXE korrigieren (realpath löst ../.. auf und prüft Existenz)
        $realPathToExe = realpath($this->ytDlpPath);

        // DEBUG 1: Prüfen ob Exe gefunden wurde
        if (!$realPathToExe || !file_exists($realPathToExe)) {
            die("FEHLER: yt-dlp.exe nicht gefunden! <br>Gesucht unter: " . $this->ytDlpPath . "<br>Absoluter Pfad wäre: " . __DIR__ . '/../../bin/yt-dlp.exe');
        }

        // Befehl bauen
        // WICHTIG: "2>&1" leitet Fehlermeldungen in den Output um, damit wir sie sehen!
        $cmd = escapeshellarg($realPathToExe) . " -f bestaudio[ext=m4a] --max-filesize 10M -o \"$tmpFile\" -x --audio-format m4a --no-playlist --quiet \"$url\" 2>&1";
        
        // Ausführen
        $output = [];
        $returnVar = 0;
        exec($cmd, $output, $returnVar);

        // DEBUG 2: Wenn es schiefgeht, Fehler ausgeben
        if ($returnVar !== 0 || !file_exists($tmpFile)) {
            echo "<h1>Fehler bei yt-dlp Ausführung (Code $returnVar)</h1>";
            echo "<strong>Befehl war:</strong> $cmd <br><br>";
            echo "<strong>Output vom System:</strong><pre>";
            print_r($output);
            echo "</pre>";
            
            // Datei aufräumen falls sie 0kb hat
            if (file_exists($tmpFile)) unlink($tmpFile);
            exit; // Script beenden damit man den Fehler sieht
        }

        return $tmpFile;
    }
}