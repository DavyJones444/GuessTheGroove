<?php
class CardRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Holt den Link anhand der ID
    public function findSongLinkById($id) {
        $stmt = $this->pdo->prepare("SELECT songlink FROM cards WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['songlink'] : null;
    }
}