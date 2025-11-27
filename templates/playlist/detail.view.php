<?php 
// Shortcuts
$playlist = $viewData['playlist'];
$cards = $viewData['cards'];
$isOwner = isset($viewData['user']) && $viewData['user']['id'] == $playlist['user_id'];

include __DIR__ . '/../layouts/header.php'; 
?>

<main>
    <script>
        function toggleImage(container, cardId) {
            const flipper = document.getElementById('flipper-' + cardId);
            flipper.classList.toggle('flipped');
        }
    </script>

    <h2 class="header-style">
        <span id="playlistName"><?= htmlspecialchars($playlist['name']) ?></span>
        <?php if ($isOwner): ?>
            <img src="/assets/icons/edit.svg" id="editButton" alt="Bearbeiten" style="width: 20px; height: 20px; cursor: pointer;" onclick="showEditForm()">
        <?php endif; ?>
    </h2>

    <?php if ($isOwner): ?>
    <div id="editForm" class="div-style" style="display: none;" >
        <input type="text" id="newPlaylistName" value="<?= htmlspecialchars($playlist['name']) ?>" required>
        <button onclick="savePlaylistName(<?= $playlist['id'] ?>)">Speichern</button>
        <button onclick="hideEditForm()">Abbrechen</button>
    </div>
    <?php endif; ?>

    <p class="div-style">Erstellt von: <?= htmlspecialchars($playlist['username']) ?></p>

    <?php if ($isOwner): ?>
        <div class="div-style" style="display: flex; gap: 15px; align-items: center; margin-top: 10px;">
            <a href="/playlists/delete?id=<?= $playlist['id'] ?>" onclick="return confirm('Playlist wirklich löschen?')" title="Playlist löschen">
                <img src="/assets/icons/delete.svg" alt="Löschen" style="width: 24px; height: 24px;">
            </a>

            <a href="/playlists/export?id=<?= $playlist['id'] ?>" title="Playlist herunterladen (PDF)">
                <img src="/assets/icons/download.svg" alt="Download" style="width: 24px; height: 24px;">
            </a>
        </div>
    <?php endif; ?>

    <section class="card-grid">
        <?php if (empty($cards)): ?>
            <p class="text-style">Diese Playlist enthält noch keine Karten.</p>
        <?php else: ?>
            <?php foreach ($cards as $card): ?>
                <div class="card">
                    <div class="flip-container" onclick="toggleImage(this, <?= $card['id'] ?>)">
                        <div class="flipper" id="flipper-<?= $card['id'] ?>">
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

                    <?php if ($isOwner): ?>
                        <a href="/cards/edit?id=<?= $card['id'] ?>" title="Bearbeiten">
                            <img src="/assets/icons/edit.svg" alt="Bearbeiten" style="width: 20px; height: 20px;">
                        </a>

                        <a href="/playlists/remove_card?playlist_id=<?= $playlist['id'] ?>&card_id=<?= $card['id'] ?>" onclick="return confirm('Karte aus Playlist entfernen?')">
                            <img src="/assets/icons/delete.svg" alt="Entfernen" style="width: 20px; height: 20px;">
                        </a>
                    <?php endif; ?>
                    
                    <a href="/cards/download?id=<?= $card['id'] ?>" title="Download">
                        <img src="/assets/icons/download.svg" alt="Download" style="width: 20px; height: 20px;">
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

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

        fetch('/playlists/update_name', { // Neue Route!
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ playlist_id: playlistId, new_name: newName })
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>