<?php
// src/Model/HitsterRepository.php

class HitsterRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createMapping($hitsterId, $songUrl) {
        $stmt = $this->pdo->prepare("INSERT INTO hitster_mapping (hitster_id, song_url) VALUES (?, ?)");
        return $stmt->execute([$hitsterId, $songUrl]);
    }

    public function getAllMappings() {
        $stmt = $this->pdo->query("SELECT * FROM hitster_mapping ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByHitsterId($hitsterId) {
        $stmt = $this->pdo->prepare("SELECT song_url FROM hitster_mapping WHERE hitster_id = ? LIMIT 1");
        $stmt->execute([$hitsterId]);
        // Gibt die URL zurück oder false
        return $stmt->fetchColumn();
    }
}