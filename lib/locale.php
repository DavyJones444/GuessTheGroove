<?php
session_start();

$lang = 'de';

if (isset($_GET['lang']) && in_array($_GET['lang'], ['de', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

if (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
}

$translations = require __DIR__ . "/../lang/$lang.php";

function t(string $key): string {
    global $translations;
    return $translations[$key] ?? $key;
}
