<?php
chdir(__DIR__ . '/..');
require 'lib/db.php';
session_start();

$loggedInUserId = $_SESSION['user_id'] ?? null;
$profileUserId = $_GET['id'] ?? $loggedInUserId;

$playlistId = $_GET['id'] ?? null;
if (!$playlistId) {
    die("Keine Playlist-ID übergeben.");
}

// Playlist abrufen
$stmt = $pdo->prepare("SELECT p.*, u.name AS username FROM playlists p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$playlistId]);
$playlist = $stmt->fetch();
if (!$playlist) die("Playlist nicht gefunden.");

// Karten der Playlist abrufen
$stmt = $pdo->prepare("SELECT c.* FROM playlist_cards pc JOIN cards c ON pc.card_id = c.id WHERE pc.playlist_id = ?");
$stmt->execute([$playlistId]);
$cards = $stmt->fetchAll();

$title = "Playlist: " . htmlspecialchars($playlist['name']);
include 'header.php';
?>

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
<main>
    <h2 class="header-style" >
        <span id="playlistName"><?= htmlspecialchars($playlist['name']) ?></span>
        <?php if ($loggedInUserId == $playlist['user_id']): ?>
            <img src="../assets/icons/edit.svg" id="editButton" alt="Bearbeiten" style="width: 20px; height: 20px; cursor: pointer;" onclick="showEditForm()">
        <?php endif; ?>
    </h2>

    <?php if ($loggedInUserId == $playlist['user_id']): ?>
    <div id="editForm" class="div-style" style="display: none;" >
        <input type="text" id="newPlaylistName" value="<?= htmlspecialchars($playlist['name']) ?>" required>
        <button onclick="savePlaylistName(<?= $playlistId ?>)">Speichern</button>
        <button onclick="hideEditForm()">Abbrechen</button>
    </div>
    <?php endif; ?>

    <script>
    function showEditForm() {
        document.getElementById('editForm').style.display = 'flex';
        document.getElementById('playlistName').style.display = 'none';
        document.getElementById('editButton').style.display = 'none';
    }

    function hideEditForm() {
        document.getElementById('editForm').style.display = 'none';
        document.getElementById('playlistName').style.display = 'inline';
        document.getElementById('editButton').style.display = 'inline';
    }

    function savePlaylistName(playlistId) {
        const newName = document.getElementById('newPlaylistName').value;

        fetch('update_playlist_name.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                playlist_id: playlistId,
                new_name: newName
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('playlistName').textContent = newName;
                hideEditForm();
            } else {
                alert(data.message || 'Fehler beim Speichern.');
            }
        })
        .catch(() => alert('Verbindungsfehler beim Speichern.'));
    }
    </script>


    <p class="div-style">Erstellt von: <?= htmlspecialchars($playlist['username']) ?></p>

    <?php if ($loggedInUserId == $playlist['user_id']): ?>
        <div class="div-style" style="display: flex; gap: 15px; align-items: center; margin-top: 10px;">
            <a href="delete_playlist.php?id=<?= $playlistId ?>" onclick="return confirm('Playlist wirklich löschen?')" title="Playlist löschen">
                <img src="../assets/icons/delete.svg" alt="Löschen" style="width: 24px; height: 24px;">
            </a>

            <a href="download_playlist.php?id=<?= $playlistId ?>" title="Playlist herunterladen (ZIP)">
                <img src="../assets/icons/download.svg" alt="Download" style="width: 24px; height: 24px;">
            </a>
        </div>
    <?php endif; ?>


    <section class="card-grid">
        <?php if (empty($cards)): ?>
            <p>Diese Playlist enthält noch keine Karten.</p>
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

                    <?php if ($loggedInUserId == $playlist['user_id']): ?>
                        <a href="../edit_card?id=<?= $card['id'] ?>" title="Bearbeiten">
                            <img src="../assets/icons/edit.svg" alt="Bearbeiten" style="width: 20px; height: 20px;">
                        </a>

                        <a href="remove_from_playlist.php?playlist_id=<?= $playlistId ?>&card_id=<?= $card['id'] ?>" onclick="return confirm('Karte nur aus dieser Playlist entfernen?')">
                            <img src="../assets/icons/delete.svg" alt="Aus Playlist entfernen" style="width: 20px; height: 20px;">
                        </a>

                    <?php endif; ?>
                    <a href="../download_card?id=<?= $card['id'] ?>" title="Download">
                        <img src="../assets/icons/download.svg" alt="Download" style="width: 20px; height: 20px;">
                    </a>
                    <?php if ($loggedInUserId == $playlist['user_id']): ?>
                            <a href="#" onclick="openPlaylistModal(<?= $card['id'] ?>)" title="Zu Playlist hinzufügen">
                                <img src="../assets/icons/playlist_add.svg" alt="Playlist hinzufügen" style="width: 20px; height: 20px;">
                            </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php include 'footer.php'; ?>
