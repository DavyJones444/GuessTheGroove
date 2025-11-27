<?php
// src/Model/CardRepository.php

class CardRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // 1. Für den Player (Scan)
    public function findSongLinkById($id) {
        $stmt = $this->pdo->prepare("SELECT songlink FROM cards WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['songlink'] : null;
    }

    // 2. Für das Profil (Alle Karten eines Users)
    public function findAllByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM cards WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Für die Welcome Page (DAS FEHLTE: Zufällige öffentliche Bilder)
    public function getPublicImages() {
        $stmt = $this->pdo->prepare("SELECT image_text, image_qr FROM cards WHERE is_public = 1 ORDER BY RAND()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Einzelne Karte holen (inkl. User-Check für Sicherheit beim Bearbeiten)
    public function getCardByIdAndUser($id, $userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM cards WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 5. Karte erstellen
    public function create($userId, $data) {
        $stmt = $this->pdo->prepare("INSERT INTO cards (user_id, title, year, artist, songlink, platform, is_public, created_at)
                               VALUES (?, ?, ?, ?, ?, ?, 0, NOW())");
        $stmt->execute([
            $userId, 
            $data['title'], 
            $data['year'], 
            $data['artist'], 
            $data['songlink'], 
            $data['platform']
        ]);
        return $this->pdo->lastInsertId();
    }

    // 6. Bilder nachtragen (nach Generierung)
    public function updateImages($id, $imageText, $imageQr) {
        $stmt = $this->pdo->prepare("UPDATE cards SET image_text = ?, image_qr = ? WHERE id = ?");
        $stmt->execute([$imageText, $imageQr, $id]);
    }

    // 7. Karte bearbeiten
    public function update($id, $userId, $data) {
        $stmt = $this->pdo->prepare("UPDATE cards SET title = ?, year = ?, artist = ?, songlink = ?, platform = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute([
            $data['title'], 
            $data['year'], 
            $data['artist'], 
            $data['songlink'], 
            $data['platform'], 
            $id, 
            $userId
        ]);
    }

    // 8. Status ändern (Öffentlich/Privat)
    public function updateStatus($id, $userId, $isPublic) {
        $stmt = $this->pdo->prepare("UPDATE cards SET is_public = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute([$isPublic, $id, $userId]);
    }

    // 9. Karte löschen
    public function delete($id, $userId) {
        // Erst aus Playlists entfernen (Constraint-Sicherheit)
        $this->pdo->prepare("DELETE FROM playlist_cards WHERE card_id = ?")->execute([$id]);
        
        $stmt = $this->pdo->prepare("DELETE FROM cards WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }

    // 10. Suche (für Entdecken-Seite)
    public function searchPublicCards($search = '', $platform = '', $year = '', $sort = '') {
        $sql = "SELECT cards.*, users.name AS creator_name, users.profile_pic, users.id AS creator_id 
                FROM cards 
                JOIN users ON cards.user_id = users.id 
                WHERE is_public = 1";

        $params = [];
        if ($search) {
            $sql .= " AND (title LIKE ? OR artist LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($platform) {
            $sql .= " AND platform = ?";
            $params[] = $platform;
        }
        if ($year) {
            $sql .= " AND year = ?";
            $params[] = $year;
        }

        switch ($sort) {
            case 'oldest': $sql .= " ORDER BY created_at ASC"; break;
            case 'newest': $sql .= " ORDER BY created_at DESC"; break;
            case 'title': $sql .= " ORDER BY title ASC"; break;
            case 'artist': $sql .= " ORDER BY artist ASC"; break;
            case 'year asc': $sql .= " ORDER BY year ASC"; break;
            case 'year desc': $sql .= " ORDER BY year DESC"; break;
            default: $sql .= " ORDER BY created_at DESC";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 11. Playlist Helper (für Batch-Upload im CardController)
    public function createPlaylist($userId, $name) {
        $stmt = $this->pdo->prepare("INSERT INTO playlists (user_id, name) VALUES (?, ?)");
        $stmt->execute([$userId, $name]);
        return $this->pdo->lastInsertId();
    }

    public function addCardToPlaylist($playlistId, $cardId) {
        $stmt = $this->pdo->prepare("INSERT INTO playlist_cards (playlist_id, card_id) VALUES (?, ?)");
        $stmt->execute([$playlistId, $cardId]);
    }
}