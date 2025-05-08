<?php
$username = '';
$profilePic = 'default_profile.png';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $username = htmlspecialchars($user['name']);
        if (!empty($user['profile_pic'])) {
            $profilePic = htmlspecialchars($user['profile_pic']);
        }
    }
}

?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/style.css">
    <title><?= $title ?? 'Guess The Groove' ?></title>
    <link rel="icon" href="https://hitstergame.com/wp-content/uploads/2022/04/cropped-favicon-32x32.png" sizes="32x32">
    <link rel="icon" href="https://hitstergame.com/wp-content/uploads/2022/04/cropped-favicon-192x192.png" sizes="192x192">
</head>
<style>
    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
        background-color: #141522;
        position: relative;
        z-index: 1000;
    }

    .burger {
        display: none;
        flex-direction: column;
        cursor: pointer;
        gap: 4px;
    }

    .burger span {
        width: 25px;
        height: 3px;
        background: white;
        transition: all 0.3s ease;
    }

    .nav-mobile {
        position: fixed;
        top: 0;
        right: -100%;
        width: 280px;
        height: 100%;
        background: #1c1c2b;
        box-shadow: -2px 0 10px rgba(0,0,0,0.4);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: right 0.4s ease;
        z-index: 999;
    }

    .nav-mobile.active {
        right: 0;
    }

    .nav-mobile .top,
    .nav-mobile .middle,
    .nav-mobile .bottom {
        padding: 20px;
    }

    .nav-mobile .top {
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 1px solid #333;
    }

    .nav-mobile .top img {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
    }

    .nav-mobile a {
        display: block;
        margin: 12px 0;
        text-decoration: none;
        font-weight: bold;
        color: white;
        transition: color 0.2s ease;
    }

    .btn-explore, .btn-play, .btn-create {
        border: 2px solid;
        border-radius: 6px;
        padding: 10px 15px;
        text-align: center;
        margin: 8px 0;
    }

    .btn-explore {
        border-color:rgb(81, 209, 255);
        box-shadow: 0 0 8px rgb(81, 209, 255);
        color: white;
    }

    .btn-play {
        border-color: #ff2bc2;
        box-shadow: 0 0 8px #ff2bc2;
        color: white;
    }

    .btn-create {
        border-color: #fffb00;
        box-shadow: 0 0 8px #fffb00;
        color: white;
    }

    .nav-mobile .bottom a {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    .close {
        font-size: 24px;
        color: white;
        cursor: pointer;
        margin-left: 15px;
    }

    @media (max-width: 768px) {
        .burger {
            display: flex;
        }

        .header-center, .profile-desktop {
            display: none;
        }
    }
</style>

<header class="header-bar">
    <a href="/welcome">
        <img src="/assets/logo.png" alt="Logo" style="height: 50px;">
    </a>

    <!-- Desktop-Buttons -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="header-center">
            <a href="/index" class="header-button btn-explore">Entdecken</a>
            <a href="/play" class="header-button btn-play">Spielen</a>
            <a href="/create_card" class="header-button btn-create">Karte erstellen</a>
        </div>
        <a href="/profile" class="profile-desktop">
            <img src="/uploads/<?= $profilePic ?>" alt="Profil" style="height: 40px; width: 40px; border-radius: 50%; object-fit: cover;">
        </a>
    <?php else: ?>
        <a href="/login" class="header-button btn-create">Einloggen</a>
    <?php endif; ?>

    <!-- Burger -->
    <div class="burger">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <!-- Mobiles Menü -->
    <nav class="nav-mobile">
        <div class="top">
            <span class="close">&#8592;</span>
            <a href="/profile">
                <img src="/uploads/<?= $profilePic ?>" alt="Profil">
            </a>
            <a href="/profile" style="color:white;"><?= $username ?></a>
        </div>
        <div class="middle">
            <a href="/index" class="btn-explore">Entdecken</a>
            <a href="/create_card" class="btn-create">Karte erstellen</a>
            <a href="/play" class="btn-play">Spielen</a>
        </div>
        <div class="bottom">
            <a href="/impressum">Impressum</a>
        </div>
    </nav>
</header>

<script>
    const burger = document.querySelector('.burger');
    const navMobile = document.querySelector('.nav-mobile');
    const closeButton = document.querySelector('.close');

    burger.addEventListener('click', () => {
        navMobile.classList.toggle('active');
    });

    closeButton.addEventListener('click', () => {
        navMobile.classList.remove('active');
    });

    document.querySelectorAll('.nav-mobile a').forEach(link => {
        link.addEventListener('click', () => {
            navMobile.classList.remove('active');
        });
    });
</script>
