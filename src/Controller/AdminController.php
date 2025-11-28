<?php
// src/Controller/AdminController.php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Middleware/AdminMiddleware.php';
require_once __DIR__ . '/../Model/HitsterRepository.php';

class AdminController extends BaseController {

    public function index() {
        // WICHTIG: Hier wird der Schutz aktiviert!
        AdminMiddleware::protect();

        $repo = new HitsterRepository($this->pdo);
        $message = $_SESSION['message'] ?? '';
        $error = $_SESSION['error'] ?? '';
        
        // Einmal anzeigen, dann löschen (Flash Messages)
        unset($_SESSION['message']);
        unset($_SESSION['error']);

        $mappings = $repo->getAllMappings();

        $this->render('admin/tool.view.php', [
            'title' => 'Admin Tool',
            'mappings' => $mappings,
            'message' => $message,
            'error' => $error
        ]);
    }

    public function store() {
        AdminMiddleware::protect();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hitsterId = trim($_POST['hitster_id'] ?? '');
            $songUrl = trim($_POST['song_url'] ?? '');

            if ($hitsterId && filter_var($songUrl, FILTER_VALIDATE_URL)) {
                $repo = new HitsterRepository($this->pdo);
                $repo->createMapping($hitsterId, $songUrl);
                $_SESSION['message'] = "Zuordnung gespeichert.";
            } else {
                $_SESSION['error'] = "Ungültige Eingabe!";
            }
        }
        
        header("Location: /admin");
        exit;
    }

    public function delete() {
        AdminMiddleware::protect(); // WICHTIG: Nur Admins dürfen löschen

        $id = $_GET['id'] ?? null;

        if ($id) {
            $repo = new HitsterRepository($this->pdo);
            $repo->deleteMapping($id);
            $_SESSION['message'] = "Eintrag gelöscht.";
        } else {
            $_SESSION['error'] = "Keine ID übergeben.";
        }

        header("Location: /admin");
        exit;
    }
}