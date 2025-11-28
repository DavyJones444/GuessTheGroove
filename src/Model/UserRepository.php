<?php
// src/Model/UserRepository.php

class UserRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByName($name) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $email, $passwordHash, $token) {
        $stmt = $this->pdo->prepare("INSERT INTO users (email, password, name, profile_pic, verified) VALUES (?, ?, ?, 'default_profile.png', 0)");
        $stmt->execute([$email, $passwordHash, $name]);
        $userId = $this->pdo->lastInsertId();

        // Token speichern
        $this->storeVerificationToken($userId, $token);
        
        return $userId;
    }

    public function storeVerificationToken($userId, $token) {
        $stmt = $this->pdo->prepare("INSERT INTO email_verifications (user_id, token) VALUES (?, ?)");
        $stmt->execute([$userId, $token]);
    }

    public function updatePassword($userId, $newHash) {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$newHash, $userId]);
    }

    public function updateName($userId, $newName) {
        $stmt = $this->pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        return $stmt->execute([$newName, $userId]);
    }

    // Login Code Logik
    public function createLoginCode($userId, $code) {
        $stmt = $this->pdo->prepare("INSERT INTO login_codes (user_id, code, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$userId, $code]);
    }

    public function verifyLoginCode($userId, $code) {
        $stmt = $this->pdo->prepare("SELECT * FROM login_codes WHERE user_id = ? AND code = ? AND created_at >= NOW() - INTERVAL 15 MINUTE ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$userId, $code]);
        return $stmt->fetch();
    }

    public function deleteLoginCode($userId) {
        $this->pdo->prepare("DELETE FROM login_codes WHERE user_id = ?")->execute([$userId]);
    }

    public function deleteUser($userId) {
        // Aufräumen (Constraints sollten das meiste erledigen, aber sicher ist sicher)
        $this->pdo->prepare("DELETE FROM email_verifications WHERE user_id = ?")->execute([$userId]);
        $this->pdo->prepare("DELETE FROM login_codes WHERE user_id = ?")->execute([$userId]);
        
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    public function updateEmail($userId, $newEmail) {
        // Prüfen, ob Email schon vergeben ist (außer vom User selbst)
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$newEmail, $userId]);
        if ($stmt->fetch()) {
            return false; // Email schon vergeben
        }

        $stmt = $this->pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        return $stmt->execute([$newEmail, $userId]);
    }
}