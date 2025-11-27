<?php
class UrlAnalyzer {
    
    public static function detectService($url) {
        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            return 'youtube';
        } elseif (strpos($url, 'spotify.com') !== false) { // Vereinfacht
            return 'spotify';
        } elseif (strpos($url, 'deezer.com') !== false) {
            return 'deezer';
        }
        return null;
    }

    public static function isHitster($url) {
        return preg_match('/hitstergame\.com\/[a-z]{2}(-[a-z]{2})?\/\d{5}/i', $url);
    }

    public static function getHitsterDetails($url) {
        return [
            'id' => preg_replace('/^.*\/(\d{5})$/', '$1', $url),
            'lang' => preg_replace('/^.*hitstergame\.com\/([a-z]{2}(?:-[a-z]{2})?)\/\d{5}$/i', '$1', $url)
        ];
    }
}