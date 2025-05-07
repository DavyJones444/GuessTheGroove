<?php
require 'lib/db.php';
session_start();

// Nur öffentliche Karten abrufen
$stmt = $pdo->prepare("SELECT image_text, image_qr FROM cards WHERE is_public = 1 ORDER BY RAND()");
$stmt->execute();
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

$publicImages = [];

foreach ($cards as $card) {
    $pair = [];
    if (!empty($card['image_text'])) {
        $pair[] = $card['image_text'];
    }
    if (!empty($card['image_qr'])) {
        $pair[] = $card['image_qr'];
    }

    // Paarweise einfügen (Reihenfolge pro Karte bleibt erhalten)
    shuffle($pair); // optional, damit QR oder Text zufällig zuerst kommt
    foreach ($pair as $img) {
        $publicImages[] = $img;
    }
}

// Ganzes Array am Ende nochmal mischen, damit QR- und Textbilder wirklich durchmischt sind
shuffle($publicImages);

$title = "Willkommen";
include 'header.php';
?>
<head>
    <style>
        .welcome-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            text-align: center;
            color: white;
            font-family: 'Maison Neue Bold', sans-serif;
        }


        .welcome-container h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .welcome-container p {
            font-size: 1.3rem;
            max-width: 600px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .welcome-container a.button {
            background-color: #141522;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1rem;
            border: 1px solid #444;
            transition: background-color 0.3s ease;
            margin: 5px;
        }

        .welcome-container a.button:hover {
            background-color: #333;
        }

        .logo-large {
            width: 600px;
            margin-bottom: 10px;
        }

        .floating-cards {
            position: fixed;
            top: 0;
            bottom: -120px;
            width: 100px;
            pointer-events: none;
            z-index: 9999; /* erhöht */
            overflow: visible;
        }

        .floating-cards.left {
            left: 0;
        }

        .floating-cards.right {
            right: 0;
        }

        .floating-card {
            position: absolute;
            bottom: 0px;
            width: 200px;  /* Größere Bilder */
            height: auto;
            opacity: 1;
            animation: float-up linear forwards;
            z-index: 9999;
            transition: opacity 0.5s ease-in-out;  /* Sanftere Übergänge */
        }

        @keyframes float-up {
            from {
                transform: translateY(0vh) scale(1) rotate(-3deg);
                opacity: 1;
            }
            30% {
                opacity: 0.7;
            }
            to {
                transform: translateY(-150vh) scale(1.05) rotate(3deg);
                opacity: 0;
            }
        }

    </style>
</head>
<body>
<div class="wrapper">
    <div class="welcome-container">
        <img src="assets/logo.png" alt="Hitster Customs Logo" class="logo-large">

        <h1>Willkommen bei <span style="color: #7da7ff;">Hitster Customs</span></h1>
        <p>
            Erstelle deine eigene digitale Hitster-Karte, teile deine Lieblingssongs und feiere Musik auf deine Art.
            Hitster Customs ist deine kreative Erweiterung zum beliebten Musikspiel!
        </p>
        <p>
            Melde dich an und werde Teil unserer Community – individuell, modern und einfach.
        </p>
        <a href="register" class="button">Jetzt registrieren</a>
        <a href="login" class="button">Ich habe schon ein Konto</a>

        <br><br><br>

        <p>
            Willst du mehr über Hitster erfahren? Dann schau auf der offiziellen Webseite vorbei!
        </p>
        <a href="https://hitstergame.com/" class="button" target="_blank">Zur offiziellen Hitster-Seite</a>
    </div>
    <div class="floating-cards left"></div>
    <div class="floating-cards right"></div>
</div>
</body>
<script>
    const images = <?= json_encode($publicImages) ?>;
    let index = 0;
    let side = 'left';

    function createFloatingCard(currentSide) {
        if (images.length === 0) return;

        const container = document.querySelector(`.floating-cards.${currentSide}`);
        const img = document.createElement("img");

        const image = images[index];
        img.src = `cards/images/${image}`;
        img.classList.add("floating-card");

        // Zufällige horizontale Position innerhalb der Seite
        img.style.left = `${Math.random() * 40 + 10}px`;

        // Zufällige Animationsdauer (zwischen 10s und 20s)
        img.style.animationDuration = `${Math.random() * 10 + 10}s`;

        container.appendChild(img);

        // Index für zyklische Wiederholung erhöhen
        index = (index + 1) % images.length;

        // Entferne Bild nach Animation (Animation dauert max. 20s)
        setTimeout(() => {
            img.remove();
        }, 20000);
    }

    // Alle 1.2 Sekunden eine neue Karte erzeugen
    setInterval(() => {
        createFloatingCard(side);
        side = side === 'left' ? 'right' : 'left'; // Abwechseln
    }, 1200);
</script>
<?php include 'footer.php'; ?>
