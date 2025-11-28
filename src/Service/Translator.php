<?php
// src/Service/Translator.php

class Translator {
    private $translations = [];
    private $lang = 'de';

    public function __construct() {
        // Sprache ermitteln (GET -> SESSION -> DEFAULT)
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['de', 'en'])) {
            $_SESSION['lang'] = $_GET['lang'];
        }

        if (isset($_SESSION['lang'])) {
            $this->lang = $_SESSION['lang'];
        }

        // Übersetzungsdatei laden
        $file = __DIR__ . "/../../lang/{$this->lang}.php";
        if (file_exists($file)) {
            $this->translations = require $file;
        }
    }

    public function get($key) {
        return $this->translations[$key] ?? $key;
    }

    public function getLang() {
        return $this->lang;
    }
}