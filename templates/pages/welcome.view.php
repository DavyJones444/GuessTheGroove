<?php 
// Wir setzen den Titel für den Header, falls header.php das erwartet
$title = $viewData['title'] ?? 'Willkommen';
include __DIR__ . '/../layouts/header.php'; 
?>

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

    /* Buttons */
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

    /* Floating Cards Animation */
    .floating-cards {
        position: fixed;
        top: 0;
        bottom: -120px;
        width: 100px;
        pointer-events: none;
        z-index: 9999;
        overflow: visible;
    }

    .floating-cards.left { left: 0; }
    .floating-cards.right { right: 0; }

    .floating-card {
        position: absolute;
        bottom: 0px;
        width: 200px;
        height: auto;
        opacity: 1;
        animation: float-up linear forwards;
        z-index: 9999;
        transition: opacity 0.5s ease-in-out;
    }

    @keyframes float-up {
        from { transform: translateY(0vh) scale(1) rotate(-3deg); opacity: 1; }
        30% { opacity: 0.7; }
        to { transform: translateY(-150vh) scale(1.05) rotate(3deg); opacity: 0; }
    }
</style>

<div id="splash-wrapper">
  <img src="/public/assets/images/logo.png" alt="Logo" id="splash-logo">
</div>

<div class="wrapper">
    <div class="welcome-container">
        <img src="/public/assets/images/logo.png" alt="Guess the Groove Logo" class="logo-large" id="logo-large">

        <h1>Willkommen bei <span style="color: #7da7ff;">Guess the Groove</span></h1>
        <p>
            Erstelle deine eigene digitale Quiz-Karte, teile deine Lieblingssongs und feiere Musik auf deine Art.
            Guess the Groove ist deine kreative Erweiterung zum beliebten Musikspiel!
        </p>
        <p>
            Melde dich an und werde Teil unserer Community – individuell, modern und einfach.
        </p>
        
        <a href="register" class="button">Jetzt registrieren</a>
        <a href="login" class="button">Ich habe schon ein Konto</a>

        <br><br><br>

        <p>
            Diese Website ist von Hitster inspiriert. <br>
            Willst du mehr über Hitster erfahren? Dann schau auf der offiziellen Webseite vorbei!
        </p>
        <a href="https://hitstergame.com/" class="button" target="_blank">Zur offiziellen Hitster-Seite</a>
    </div>
    
    <div class="floating-cards left"></div>
    <div class="floating-cards right"></div>
</div>

<script>
    document.body.classList.add("lock-scroll");

    window.addEventListener("wheel", startZoomAnimation, { once: true});
    window.addEventListener("click", startZoomAnimation, { once: true});

    // Hier greifen wir auf die Daten vom Controller zu
    const images = <?= json_encode($viewData['publicImages']) ?>;
    
    let index = 0;
    let side = 'left';

    function startZoomAnimation() {
        const splashLogo = document.getElementById("splash-logo");
        const staticLogo = document.getElementById("logo-large");

        const splashRect = splashLogo.getBoundingClientRect();
        const targetRect = staticLogo.getBoundingClientRect();

        const deltaX = targetRect.left - splashRect.left;
        const deltaY = targetRect.top - splashRect.top;
        const scale = targetRect.width / splashRect.width;

        splashLogo.style.transform = `translate(${deltaX}px, ${deltaY}px) scale(${scale})`;

        setTimeout(() => {
            const wrapper = document.getElementById("splash-wrapper");
            if(wrapper) {
                wrapper.style.opacity = "0";
                setTimeout(() => {
                    wrapper.remove();
                    document.body.classList.remove("lock-scroll");
                }, 500);
            }
        }, 800);
    }

    function createFloatingCard(currentSide) {
        if (!images || images.length === 0) return;

        const container = document.querySelector(`.floating-cards.${currentSide}`);
        if (!container) return;
        
        const img = document.createElement("img");

        const image = images[index];
        
        // ACHTUNG: Pfad prüfen! Wenn wir auf public/ umgestellt haben, 
        // und die Bilder in public/uploads/cards liegen:
        img.src = `/uploads/cards/${image}`; 
        // Falls du die Ordner noch nicht verschoben hast, lass es auf `card/images/`
        
        img.classList.add("floating-card");

        // Zufallswerte
        img.style.left = `${Math.random() * 40 + 10}px`;
        img.style.animationDuration = `${Math.random() * 10 + 10}s`;

        container.appendChild(img);

        // Index erhöhen
        index = (index + 1) % images.length;

        // Cleanup
        setTimeout(() => {
            img.remove();
        }, 20000);
    }

    // Interval starten
    setInterval(() => {
        createFloatingCard(side);
        side = side === 'left' ? 'right' : 'left';
    }, 1200);
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>