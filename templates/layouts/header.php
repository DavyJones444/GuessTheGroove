<?php
// Fallback, falls keine User-Daten übergeben wurden
$currentUser = $viewData['user'] ?? null;
$pageTitle = $viewData['title'] ?? 'Guess The Groove';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/style.css">
    
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <link rel="icon" href="https://hitstergame.com/wp-content/uploads/2022/04/cropped-favicon-32x32.png" sizes="32x32">
    <link rel="icon" href="https://hitstergame.com/wp-content/uploads/2022/04/cropped-favicon-192x192.png" sizes="192x192">
</head>
<body>

<header class="header-bar">
    <a href="/welcome">
        <img src="/assets/images/logo_solo.png" alt="Logo" style="height: 50px;">
    </a>

    <?php if ($currentUser): ?>
        <div class="header-center">
            <a href="/home" class="header-button btn-explore">Entdecken</a>
            <a href="/play" class="header-button btn-play">Spielen</a>
            <a href="/cards/create" class="header-button btn-create">Karte erstellen</a>
        </div>
        <a href="/profile" class="profile-desktop">
            <img src="/uploads/<?= htmlspecialchars($currentUser['profile_pic'] ?? 'default_profile.png') ?>" 
                 alt="Profil" 
                 style="height: 40px; width: 40px; border-radius: 50%; object-fit: cover;">
        </a>
    <?php else: ?>
        <a href="/login" class="header-button btn-create">Einloggen</a>
    <?php endif; ?>

    <div class="burger">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <nav class="nav-mobile">
        <div class="top">
            <span class="close">&#8592;</span>
            <?php if ($currentUser): ?>
                <a href="/profile">
                    <img src="/uploads/<?= htmlspecialchars($currentUser['profile_pic'] ?? 'default_profile.png') ?>" alt="Profil">
                </a>
                <a href="/profile" style="color:white;"><?= htmlspecialchars($currentUser['name']) ?></a>
            <?php else: ?>
                <a href="/login">Nicht eingeloggt</a>
            <?php endif; ?>
        </div>
        <div class="middle">
            <a href="/" class="btn-explore">Entdecken</a>
            <a href="/cards/create" class="btn-create">Karte erstellen</a>
            <a href="/play" class="btn-play">Spielen</a>
        </div>
        <div class="bottom">
            <a href="/impressum">Impressum</a>
        </div>
    </nav>
</header>

<script>
    // Kleines Inline-JS ist hier okay, oder besser nach /js/main.js verschieben
    const burger = document.querySelector('.burger');
    const navMobile = document.querySelector('.nav-mobile');
    const closeButton = document.querySelector('.close');

    if(burger) {
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
    }
</script>