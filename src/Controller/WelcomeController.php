<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Model/CardRepository.php';

class WelcomeController extends BaseController{

    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    public function index() {
        $repo = new CardRepository($this->pdo);
        $cards = $repo->getPublicImages();

        $publicImages = [];

        // Die Logik zum Mischen und Zusammenfügen
        foreach ($cards as $card) {
            $pair = [];
            if (!empty($card['image_text'])) {
                $pair[] = $card['image_text'];
            }
            if (!empty($card['image_qr'])) {
                $pair[] = $card['image_qr'];
            }

            // Paarweise mischen
            shuffle($pair); 
            foreach ($pair as $img) {
                $publicImages[] = $img;
            }
        }

        // Alles nochmal final mischen
        shuffle($publicImages);

        // Daten an die View übergeben
        $viewData = [
            'publicImages' => $publicImages,
            'title' => 'Willkommen'
        ];

        // View laden
        require __DIR__ . '/../../templates/pages/welcome.view.php';
    }
}