<?php
chdir(__DIR__ . '/..');

require 'lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome");
    exit();
}

$loggedInUserId = $_SESSION['user_id'] ?? null;

// Profilinformationen des eingeloggten Nutzers laden (für Header)
if ($loggedInUserId) {
    $stmt = $pdo->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$loggedInUserId]);
    $loggedInUser = $stmt->fetch();

    $username = $loggedInUser['name'] ?? 'Unbekannt';
    $profilePic = $loggedInUser['profile_pic'] ?? 'default_profile.png';
}


$profileUserId = $_GET['id'] ?? $loggedInUserId;

if (!$profileUserId) {
    die("Kein Benutzer angegeben und nicht eingeloggt.");
}

// Benutzerdaten abrufen
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profileUserId]);
$user = $stmt->fetch();
if (!$user) die("Benutzer nicht gefunden.");

// Karten dieses Nutzers abrufen
$stmt = $pdo->prepare("SELECT * FROM cards WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$profileUserId]);
$cards = $stmt->fetchAll();

$title = "Profil von " . htmlspecialchars($user['name']);

// Playlists des eingeloggten Nutzers laden
$playlists = [];
if ($loggedInUserId == $profileUserId) {
    $stmt = $pdo->prepare("SELECT * FROM playlists WHERE user_id = ?");
    $stmt->execute([$loggedInUserId]);
    $playlists = $stmt->fetchAll();
}
include 'header.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <script>
        function toggleImage(container, cardId) {
            const flipper = document.getElementById('flipper-' + cardId);
            flipper.classList.toggle('flipped');
        }
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
        }
    </style>
</head>
<body>
<main>
    <?php if (!empty($_SESSION['message'])): ?>
        <div id="floating-message" class="floating-message">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <div class="profile-header">
        <span class="profile-label">Profil von</span>
        <img src="../uploads/<?= htmlspecialchars($user['profile_pic'] ?? 'default_profile.png') ?>"
            alt="Profilbild"
            class="profile-picture">
        <span class="profile-name"><?= htmlspecialchars($user['name']) ?></span>
    </div>

    <div class="card-carousel-wrapper">
        <button id="scroll-left" class="carousel-button">&#8592;</button>
        <div class="card-carousel">
            <section class="card-grid horizontal-scroll">
                <?php if (empty($cards)): ?>
                    <p>Erstelle ein paar Karten, sonst ist es hier so leer...</p>
                <?php else: ?>
                    <?php foreach ($cards as $card): ?>
                        <div class="card">
                            <div class="flip-container" onclick="toggleImage(this, <?= $card['id'] ?>)">
                                <div class="flipper" id="flipper-<?= $card['id'] ?>">
                                    <div class="front">
                                        <img src="../cards/images/<?= htmlspecialchars($card['image_text']) ?>" alt="Bild">
                                    </div>
                                    <div class="back">
                                        <img src="../cards/images/<?= htmlspecialchars($card['image_qr']) ?>" alt="QR-Code">
                                    </div>
                                </div>
                            </div>

                            <!-- Abstand hinzufügen -->
                            <div style="margin: 10px 0;"></div>
                            <p><strong><?= htmlspecialchars($card['title']) ?></strong> (<?= htmlspecialchars($card['year']) ?>)</p>
                            <p><?= htmlspecialchars($card['artist']) ?> – <?= htmlspecialchars($card['platform']) ?></p>

                            <a href="<?= $card['songlink'] ?>" title="Songlink" target="_blank" rel="noopener noreferrer">
                                <img src="../assets/icons/music_note.svg" alt="Songlink" style="width: 20px; height: 20px;">
                            </a>

                            <?php if ($loggedInUserId == $profileUserId): ?>
                                <a href="../edit_card?id=<?= $card['id'] ?>" title="Bearbeiten">
                                    <img src="../assets/icons/edit.svg" alt="Bearbeiten" style="width: 20px; height: 20px;">
                                </a>

                                <a href="../delete_card?id=<?= $card['id'] ?>" onclick="return confirm('Wirklich löschen?')">
                                    <img src="../assets/icons/delete.svg" alt="Löschen" style="width: 20px; height: 20px;">
                                </a>
                            <?php endif; ?>
                            <a href="../download_card.php?id=<?= $card['id'] ?>" title="Download">
                                <img src="../assets/icons/download.svg" alt="Download" style="width: 20px; height: 20px;">
                            </a>
                            <?php if ($loggedInUserId == $profileUserId): ?>
                                <a>
                                    <span 
                                        id="status-<?= $card['id'] ?>" 
                                        class="status-toggle"
                                        data-card-id="<?= $card['id'] ?>" 
                                        data-status="<?= $card['is_public'] ?>" 
                                        title="<?= $card['is_public'] == 1 ? 'Öffentlich' : 'Privat' ?>"
                                    >
                                        <img 
                                            src="../assets/icons/<?= $card['is_public'] == 1 ? 'public.svg' : 'public_off.svg' ?>" 
                                            alt="<?= $card['is_public'] == 1 ? 'Öffentlich' : 'Privat' ?>" 
                                            style="width: 20px; height: 20px;"
                                        >
                                    </span>

                                    <a onclick="openModal(<?= $card['id'] ?>)" title="Zu Playlist hinzufügen">
                                        <img src="../assets/icons/playlist_add.svg" alt="Zur Playlist" style="width: 20px; height: 20px;">
                                    </a>


                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </section>
            </div>
        <button id="scroll-right" class="carousel-button">&#8594;</button>
    </div>

    <button id="toggleViewBtn" class="button">Alle anzeigen</button>

    <?php if (!empty($playlists)): ?>
        <h3 class="header-style">Deine Playlists</h3>
        <section class="playlist-grid">
            <?php foreach ($playlists as $playlist): ?>
                <div class="card">
                    <a href="playlist/playlist_detail.php?id=<?= $playlist['id'] ?>">
                        <?= htmlspecialchars($playlist['name']) ?>
                    </a>
                    <br>
                    <a href="playlist/playlist_detail.php?id=<?= $playlist['id'] ?>">
                        <img src="../assets/icons/edit.svg" alt="Bearbeiten" style="width: 20px; height: 20px;">
                    </a>
                    <a href="playlist/delete_playlist.php?id=<?= $playlist['id'] ?>" onclick="return confirm('Wirklich löschen?')">
                        <img src="../assets/icons/delete.svg" alt="Löschen" style="width: 20px; height: 20px;">
                    </a>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>


    <!-- Modal für Playlist -->
    <div id="playlistModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="$('#playlistModal').hide()">&times;</span>
            <h3>Karte zu Playlist hinzufügen</h3>
            <form method="POST" action="playlist/add_card_to_playlist.php">
                <input type="hidden" name="card_id" id="modal-card-id">
                
                <label for="playlist_id">Vorhandene Playlist:</label>
                <select name="playlist_id" id="playlist-select">
                    <?php foreach ($playlists as $pl): ?>
                        <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Hinzufügen</button>
            </form>

            <hr>

            <form method="POST" action="playlist/create_playlist.php">
                <h4>Neue Playlist erstellen</h4>
                <input type="hidden" name="card_id" id="modal-create-card-id">
                <input type="text" name="name" placeholder="Playlist-Name" required>
                <button type="submit">Erstellen & Hinzufügen</button>
            </form>
        </div>
    </div>


    <?php if ($loggedInUserId == $profileUserId): ?>
        <h3 class="header-style">Account-Einstellungen</h3>
        <div class="div-style">Benutzername ändern</div>
        <form method="post" action="update_profile" class="form-container">
            <input type="text" name="username" class="input_field">
            <button type="submit" class="button">Ändern</button>
        </form>

        <!-- Abstand hinzufügen -->
        <div style="margin: 30px 0;"></div>

        <div class="div-style">Passwort ändern</div>
        <form method="post" action="change_password" class="form-container">
            <input type="password" name="current_password" placeholder="Aktuelles Passwort" required class="input-field">
            <input type="password" name="new_password" placeholder="Neues Passwort" required class="input-field">
            <input type="password" name="confirm_password" placeholder="Neues Passwort wiederholen" required class="input-field">
            <button type="submit" class="button">Passwort ändern</button>
        </form>
        
        <!-- Abstand hinzufügen -->
        <div style="margin: 30px 0;"></div>

        <div class="div-style">Profilbild ändern</div>
        <form action="upload_profile_pic" method="post" enctype="multipart/form-data" class="form-container">
            <input type="file" name="profile_pic" accept="image/*" required class="button">
            <button type="submit" class="button">Hochladen</button>
        </form>
        
        <!-- Abstand hinzufügen -->
        <div style="margin: 30px 0;"></div>
        
        <div class="div-style">Account Optionen</div>
        <form method="post" action="logout" class="form-container">
            <button type="submit" class="button">Abmelden</button>
        </form>
        <form method="post" action="delete_account" onsubmit="return confirm('Account wirklich löschen?')" class="form-container">
            <button type="submit" class="button">Account löschen</button>
        </form>
    <?php endif; ?>

</main>
<script>

    function openModal(cardId) {
        document.getElementById('playlistModal').style.display = 'flex';
        document.getElementById('modal-card-id').value = cardId;
        document.getElementById('modal-create-card-id').value = cardId;
    }

    function closeModal() {
        document.getElementById('playlistModal').style.display = 'none';
    }

    setTimeout(function() {
        const msg = document.getElementById('floating-message');
        if (msg) {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }
    }, 3000); // nach 3 Sekunden ausblenden

    // Status wechseln, wenn auf das Wort geklickt wird
    $(document).on('click', '.status-toggle', function() {
        var cardId = $(this).data('card-id');
        var currentStatus = $(this).data('status');
        var newStatus = currentStatus == 1 ? 0 : 1;  // Status wechseln (0 -> 1 oder 1 -> 0)
        
        // AJAX-Anfrage zum Aktualisieren des Status
        $.post('update_card_status', {
            card_id: cardId,
            is_public: newStatus
        }, function(response) {
            if (response.success) {
                var newStatus = currentStatus == 1 ? 0 : 1;
                var newSrc = newStatus == 1 ? '../assets/icons/public.svg' : '../assets/icons/public_off.svg';
                var newAlt = newStatus == 1 ? 'Öffentlich' : 'Privat';

                var statusSpan = $('#status-' + cardId);
                var icon = statusSpan.find('img');

                statusSpan.data('status', newStatus);
                statusSpan.attr('title', newAlt); // Tooltip aktualisieren

                // Animation: Bild ausblenden, Quelle wechseln, einblenden
                icon.fadeOut(80, function () {
                    icon.attr('src', newSrc).attr('alt', newAlt).fadeIn(80);
                });
            } else {
                alert('Fehler beim Aktualisieren des Status.');
            }
        }, 'json');
    });

    // Schließt das Modal, wenn man außerhalb des Inhalts klickt
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('playlistModal');
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
</script>
<script>
    const scrollLeftBtn = document.getElementById('scroll-left');
    const scrollRightBtn = document.getElementById('scroll-right');
    const toggleBtn = document.getElementById('toggleViewBtn');
    let expanded = false;
    const carousel = document.querySelector('.card-carousel');

    scrollLeftBtn.addEventListener('click', () => {
        carousel.scrollBy({ left: -300, behavior: 'smooth' });
    });

    scrollRightBtn.addEventListener('click', () => {
        carousel.scrollBy({ left: 300, behavior: 'smooth' });
    });

    toggleBtn.addEventListener('click', () => {
        const grid = document.querySelector('.card-grid');
        if (!expanded) {
            grid.classList.remove('horizontal-scroll');
            grid.style.flexWrap = 'wrap';
            toggleBtn.textContent = "Weniger anzeigen";
            scrollLeftBtn.style.display = 'none';
            scrollRightBtn.style.display = 'none';
        } else {
            grid.classList.add('horizontal-scroll');
            grid.style.flexWrap = 'nowrap';
            toggleBtn.textContent = "Alle anzeigen";
            scrollLeftBtn.style.display = 'flex';
            scrollRightBtn.style.display = 'flex';
        }
        expanded = !expanded;
    });
</script>

</body>
</html>
<?php include 'footer.php'; ?>
