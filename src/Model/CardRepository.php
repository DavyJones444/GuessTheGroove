<?php
class CardRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getPublicImages() {
        // Nur öffentliche Karten abrufen
        $stmt = $this->pdo->prepare("SELECT image_text, image_qr FROM cards WHERE is_public = 1 ORDER BY RAND()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Holt den Link anhand der ID
    public function findSongLinkById($id) {
        $stmt = $this->pdo->prepare("SELECT songlink FROM cards WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['songlink'] : null;
    }
}