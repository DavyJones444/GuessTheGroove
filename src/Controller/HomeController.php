<?php
// src/Controller/HomeController.php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Model/CardRepository.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class HomeController extends BaseController {
    
    public function index() {
        AuthMiddleware::protect(); // Startseite ist nur für eingeloggte User

        $cardRepo = new CardRepository($this->pdo);

        // Filter Parameter
        $search = $_GET['search'] ?? '';
        $platform = $_GET['platform'] ?? '';
        $year = $_GET['year'] ?? '';
        $sort = $_GET['sort'] ?? '';

        $cards = $cardRepo->searchPublicCards($search, $platform, $year, $sort);

        $this->render('pages/home.view.php', [
            'title' => 'Entdecken',
            'cards' => $cards,
            'filters' => [
                'search' => $search,
                'platform' => $platform,
                'year' => $year,
                'sort' => $sort
            ],
            'verified' => isset($_GET['verified'])
        ]);
    }
}