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
        // NEU: Toleranter Regex, der auch Zwischensegmente (wie /aaaa0040/) erlaubt
        // Wir suchen nach: hitstergame.com -> irgendwas -> 5 Ziffern am Ende
        return preg_match('/hitstergame\.com\/.*\d{5}$/i', $url);
    }

    public static function getHitsterDetails($url) {
        return [
            // Nimmt einfach die letzten 5 Ziffern am Ende der URL
            'id' => preg_replace('/^.*\/(\d{5})$/', '$1', $url),
            
            // Versucht die Sprache zu finden (de, en-us, etc.), Standard 'de'
            'lang' => preg_match('/\/([a-z]{2}(?:-[a-z]{2})?)\//', $url, $m) ? $m[1] : 'de'
        ];
    }
}