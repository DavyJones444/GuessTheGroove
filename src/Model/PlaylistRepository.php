<?php
// src/Model/PlaylistRepository.php

class PlaylistRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findAllByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM playlists WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT p.*, u.name AS username FROM playlists p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($userId, $name) {
        $stmt = $this->pdo->prepare("INSERT INTO playlists (user_id, name) VALUES (?, ?)");
        $stmt->execute([$userId, $name]);
        return $this->pdo->lastInsertId();
    }

    public function updateName($id, $newName) {
        $stmt = $this->pdo->prepare("UPDATE playlists SET name = ? WHERE id = ?");
        return $stmt->execute([$newName, $id]);
    }

    public function delete($id) {
        // FK Constraints sollten playlist_cards automatisch löschen, aber sicher ist sicher:
        $this->pdo->prepare("DELETE FROM playlist_cards WHERE playlist_id = ?")->execute([$id]);
        
        $stmt = $this->pdo->prepare("DELETE FROM playlists WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Karten-Beziehungen ---

    public function getCardsInPlaylist($playlistId) {
        $stmt = $this->pdo->prepare("
            SELECT c.* FROM playlist_cards pc 
            JOIN cards c ON pc.card_id = c.id 
            WHERE pc.playlist_id = ?
        ");
        $stmt->execute([$playlistId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCard($playlistId, $cardId) {
        // Prüfen ob schon existiert
        $stmt = $this->pdo->prepare("SELECT 1 FROM playlist_cards WHERE playlist_id = ? AND card_id = ?");
        $stmt->execute([$playlistId, $cardId]);
        if (!$stmt->fetch()) {
            $stmt = $this->pdo->prepare("INSERT INTO playlist_cards (playlist_id, card_id) VALUES (?, ?)");
            return $stmt->execute([$playlistId, $cardId]);
        }
        return false;
    }

    public function removeCard($playlistId, $cardId) {
        $stmt = $this->pdo->prepare("DELETE FROM playlist_cards WHERE playlist_id = ? AND card_id = ?");
        return $stmt->execute([$playlistId, $cardId]);
    }
    
    public function getCardCount($playlistId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM playlist_cards WHERE playlist_id = ?");
        $stmt->execute([$playlistId]);
        return $stmt->fetchColumn();
    }
}