<?php 
$filters = $viewData['filters'];
$cards = $viewData['cards'];
$verified = $viewData['verified'];

include __DIR__ . '/../layouts/header.php'; 
?>

<?php if ($verified): ?>
    <div class="success-message">Erfolgreich verifiziert!</div>
<?php endif; ?>

<main>
    <form method="get" action="/" class="form-container">
        <input type="text" name="search" placeholder="Titel oder Künstler" 
               value="<?= htmlspecialchars($filters['search']) ?>" class="input-field">
        
        <select name="platform" class="select-field">
            <option value="">Plattform wählen</option>
            <option value="Spotify" <?= $filters['platform'] === 'Spotify' ? 'selected' : '' ?>>Spotify</option>
            <option value="Deezer" <?= $filters['platform'] === 'Deezer' ? 'selected' : '' ?>>Deezer</option>
            <option value="YouTube" <?= $filters['platform'] === 'YouTube' ? 'selected' : '' ?>>YouTube</option>
            <option value="Andere" <?= $filters['platform'] === 'Andere' ? 'selected' : '' ?>>Andere</option>
        </select>
        
        <input type="number" name="year" placeholder="Jahr" 
               value="<?= htmlspecialchars($filters['year']) ?>" class="input-field">
        
        <select name="sort" class="select-field">
            <option value="">Sortieren nach</option>
            <option value="title" <?= $filters['sort'] === 'title' ? 'selected' : '' ?>>Titel</option>
            <option value="artist" <?= $filters['sort'] === 'artist' ? 'selected' : '' ?>>Künstler</option>
            <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Älteste zuerst</option>
            <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Neuste zuerst</option>
            <option value="year asc" <?= $filters['sort'] === 'year asc' ? 'selected' : '' ?>>Jahr aufsteigend</option>
            <option value="year desc" <?= $filters['sort'] === 'year desc' ? 'selected' : '' ?>>Jahr absteigend</option>
        </select>
        
        <button type="submit" class="button">Filtern</button>
    </form>

    <section class="card-grid">
        <?php if (empty($cards)): ?>
            <p class="text-style">Es gibt keine öffentlichen Karten, die deinen Suchkriterien entsprechen.</p>
        <?php else: ?>
            <?php foreach ($cards as $card): ?>
                <div class="card">
                    <div class="flip-container" onclick="this.querySelector('.flipper').classList.toggle('flipped');">
                        <div class="flipper">
                            <div class="front">
                                <img src="/uploads/cards/<?= htmlspecialchars($card['image_text']) ?>" alt="Bild">
                            </div>
                            <div class="back">
                                <img src="/uploads/cards/<?= htmlspecialchars($card['image_qr']) ?>" alt="QR-Code">
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin: 10px 0;"></div>
                    <p><strong><?= htmlspecialchars($card['title']) ?></strong> (<?= htmlspecialchars($card['year']) ?>)</p>
                    <p><?= htmlspecialchars($card['artist']) ?> – <?= htmlspecialchars($card['platform']) ?></p>
                    
                    <a href="<?= $card['songlink'] ?>" title="Songlink" target="_blank" rel="noopener noreferrer">
                        <img src="/assets/icons/music_note.svg" alt="Songlink" style="width: 20px; height: 20px;">
                    </a>

                    <a href="/cards/download?id=<?= $card['id'] ?>" title="Download">
                        <img src="/assets/icons/download.svg" alt="Download" style="width: 20px; height: 20px;">
                    </a>
                    
                    <div class="creator-info" style="display: flex; align-items: center; justify-content: center; margin-top: 10px;">
                        <?php 
                            $creatorPic = !empty($card['profile_pic']) 
                                ? '/uploads/' . $card['profile_pic'] 
                                : '/assets/images/default_profile.png';
                        ?>
                        <img src="<?= htmlspecialchars($creatorPic) ?>" 
                            alt="Profilbild" 
                            style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 8px;">
                        
                        <a href="/profile?id=<?= $card['creator_id'] ?>" style="text-decoration: none; color: #ffffff; font-size: 0.9em;">
                            <?= htmlspecialchars($card['creator_name']) ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<script>
    // Message ausblenden
    setTimeout(function () {
        const msg = document.querySelector('.success-message');
        if (msg) {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }
    }, 3000);
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>