<?php
$host = 'localhost'; // default: localhost | for homeserver only: db
$db   = 'guess_the_groove_db';
$user = 'root'; // default: root | for homeserver only: myuser
$pass = ''; // default: '' | for homeserver only: mypass
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Verbindung fehlgeschlagen: " . $e->getMessage());
}