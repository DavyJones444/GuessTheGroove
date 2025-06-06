<?php
require 'lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit();
}

// Such-/Filter-Logik
$search = $_GET['search'] ?? '';
$platform = $_GET['platform'] ?? '';
$year = $_GET['year'] ?? '';
$sort = $_GET['sort'] ?? '';

$sql = "SELECT cards.*, users.name AS creator_name, users.profile_pic, users.id AS creator_id 
        FROM cards 
        JOIN users ON cards.user_id = users.id 
        WHERE is_public = 1";

$params = [];
if ($search) {
    $sql .= " AND (title LIKE ? OR artist LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($platform) {
    $sql .= " AND platform = ?";
    $params[] = $platform;
}
if ($year) {
    $sql .= " AND year = ?";
    $params[] = $year;
}
switch ($sort) {
    case 'oldest': $sql .= " ORDER BY created_at ASC"; break;
    case 'newest': $sql .= " ORDER BY created_at DESC"; break;
    case 'title': $sql .= " ORDER BY title ASC"; break;
    case 'artist': $sql .= " ORDER BY artist ASC"; break;
    case 'year asc': $sql .= " ORDER BY year ASC"; break;
    case 'year desc': $sql .= " ORDER BY year DESC"; break;
    default: $sql .= " ORDER BY created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cards = $stmt->fetchAll();

$title = "Entdecken";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="de">
<body>
    <?php if (isset($_GET['verified']) && $_GET['verified'] == '1'): ?>
        <div class="success-message">Erfolgreich verifiziert!</div>
    <?php endif; ?>
<main>
    <form method="get" class="form-container">
        <input type="text" name="search" placeholder="Titel oder Künstler" value="<?= htmlspecialchars($search) ?>" class="input-field">
        <select name="platform" class="select-field">
            <option value="">Plattform wählen</option>
            <option value="Spotify" <?= $platform === 'Spotify' ? 'selected' : '' ?>>Spotify</option>
            <option value="Deezer" <?= $platform === 'Deezer' ? 'selected' : '' ?>>Deezer</option>
            <option value="YouTube" <?= $platform === 'YouTube' ? 'selected' : '' ?>>YouTube</option>
            <option value="Andere" <?= $platform === 'Andere' ? 'selected' : '' ?>>Andere</option>
        </select>
        <input type="number" name="year" placeholder="Jahr" value="<?= htmlspecialchars($year) ?>" class="input-field">
        <select name="sort" class="select-field">
            <option value="">Sortieren nach</option>
            <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Titel</option>
            <option value="artist" <?= $sort === 'artist' ? 'selected' : '' ?>>Künstler</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Älteste zuerst</option>
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Neuste zuerst</option>
            <option value="year asc" <?= $sort === 'year asc' ? 'selected' : '' ?>>Jahr aufsteigend</option>
            <option value="year desc" <?= $sort === 'year desc' ? 'selected' : '' ?>>Jahr absteigend</option>
        </select>
        <button type="submit" class="button">Filtern</button>
    </form>

    <section class="card-grid">
        <?php if (empty($cards)): ?>
            <p>Es gibt keine öffentlichen Karten, die deinen Suchkriterien entsprechen.</p>
        <?php else: ?>
            <?php foreach ($cards as $card): ?>
                <div class="card">
                    <div class="flip-container" onclick="toggleImage(this, <?= $card['id'] ?>)">
                        <div class="flipper" id="flipper-<?= $card['id'] ?>">
                            <div class="front">
                                <img src="card/images/<?= htmlspecialchars($card['image_text']) ?>" alt="Bild">
                            </div>
                            <div class="back">
                                <img src="card/images/<?= htmlspecialchars($card['image_qr']) ?>" alt="QR-Code">
                            </div>
                        </div>
                    </div>
                    <p><strong><?= htmlspecialchars($card['title']) ?></strong> (<?= htmlspecialchars($card['year']) ?>)</p>
                    <p><?= htmlspecialchars($card['artist']) ?> – <?= htmlspecialchars($card['platform']) ?></p>
                    
                    <a href="<?= $card['songlink'] ?>" title="Songlink" target="_blank" rel="noopener noreferrer">
                        <img src="assets/icons/music_note.svg" alt="Songlink" style="width: 20px; height: 20px;">
                    </a>

                    <a href="card/download_card.php?id=<?= $card['id'] ?>" title="Download">
                        <img src="assets/icons/download.svg" alt="Download" style="width: 20px; height: 20px;">
                    </a>
                    
                    <div class="creator-info" style="display: flex; align-items: center; margin-top: 5px;">
                        <img src="uploads/<?= htmlspecialchars($card['profile_pic'] ?? 'default_profile.png') ?>" 
                            alt="Profilbild" 
                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                        <a href="profile?id=<?= $card['creator_id'] ?>" 
                        style="text-decoration: none; color: #ffffff;">
                        <?= htmlspecialchars($card['creator_name']) ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
<script>
    setTimeout(function () {
        const msg = document.querySelector('.success-message');
        if (msg) {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }
    }, 3000);

    function toggleImage(container, cardId) {
        const flipper = document.getElementById('flipper-' + cardId);
        flipper.classList.toggle('flipped');
    }
</script>
</body>
</html>
<?php include 'footer.php'; ?>
