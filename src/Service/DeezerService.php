<?php
// src/Service/DeezerService.php

class DeezerService {
    public function getTrack($id) {
        $url = "https://api.deezer.com/track/$id";
        return @file_get_contents($url);
    }

    public function search($query) {
        $url = "https://api.deezer.com/search?q=" . urlencode($query);
        return @file_get_contents($url);
    }
}