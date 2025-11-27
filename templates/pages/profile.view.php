<?php 
// Shortcuts für einfacheres Schreiben
$user = $viewData['profileUser'];
$cards = $viewData['cards'];
$playlists = $viewData['playlists'];
$isOwner = $viewData['isOwner'];
$message = $viewData['message'] ?? null;
$batchSuccess = $viewData['batchSuccess'] ?? false;

include __DIR__ . '/../layouts/header.php'; 
?>

<style>
    /* Carousel Buttons nur einblenden wenn JS aktiv ist */
    .card-carousel-wrapper { position: relative; display: flex; align-items: center; }
    .card-carousel { overflow-x: auto; display: flex; gap: 15px; padding: 10px; scroll-behavior: smooth; width: 100%; }
    .card-carousel::-webkit-scrollbar { height: 8px; }
    .card-carousel::-webkit-scrollbar-thumb { background: #444; border-radius: 4px; }
    .carousel-button { background: rgba(0,0,0,0.5); color: white; border: none; font-size: 2rem; cursor: pointer; padding: 0 10px; z-index: 10; }
    .horizontal-scroll { flex-wrap: nowrap; }
</style>

<main>
    <?php if ($message): ?>
        <div id="floating-message" class="floating-message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($batchSuccess): ?>
        <div class="success-message" style="position: relative; margin-bottom: 20px;">
            Playlist und Karten erfolgreich erstellt!
        </div>
    <?php endif; ?>

    <div class="profile-header">
        <div style="position: relative; display: inline-block;">
            <?php 
                $profilePicPath = !empty($user['profile_pic']) 
                    ? '/uploads/' . $user['profile_pic'] 
                    : '/assets/images/default_profile.png';
            ?>
            <img src="<?= htmlspecialchars($profilePicPath) ?>" 
                 alt="Profilbild" 
                 class="profile-picture"
                 style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; <?= $isOwner ? 'cursor: pointer;' : '' ?>"
                 <?php if ($isOwner): ?>onclick="document.getElementById('upload-input').click();"<?php endif; ?>
            >
            
            <?php if ($isOwner): ?>
                <form action="/profile/upload" method="post" enctype="multipart/form-data" style="display: none;">
                    <input type="file" name="profile_pic" id="upload-input" onchange="this.form.submit()" accept="image/*">
                </form>
            <?php endif; ?>
        </div>
        
        <div>
            <div class="profile-label">Profil von</div>
            <div class="profile-name"><?= htmlspecialchars($user['name']) ?></div>
        </div>
    </div>

    <div class="wrapper">
        <h2 class="header-style">Kartensammlung</h2>

        <?php if (empty($cards)): ?>
            <p class="text-style">
                <?= $isOwner ? 'Du hast noch keine Karten.' : 'Dieser Nutzer hat noch keine Karten.' ?>
                <?php if ($isOwner): ?> <a href="/cards/create">Jetzt erstellen!</a> <?php endif; ?>
            </p>
        <?php else: ?>
            
            <div class="card-carousel-wrapper">
                <button id="scroll-left" class="carousel-button" style="display:none;">&#8592;</button>
                
                <div class="card-carousel">
                    <section class="card-grid horizontal-scroll" id="cardGrid">
                        <?php foreach ($cards as $card): ?>
                            <div class="card">
                                <div class="flip-container" onclick="this.querySelector('.flipper').classList.toggle('flipped');">
                                    <div class="flipper" id="flipper-<?= $card['id'] ?>">
                                        <div class="front">
                                            <img src="/uploads/cards/<?= htmlspecialchars($card['image_text']) ?>" alt="Front">
                                        </div>
                                        <div class="back">
                                            <img src="/uploads/cards/<?= htmlspecialchars($card['image_qr']) ?>" alt="Back">
                                        </div>
                                    </div>
                                </div>

                                <div style="margin: 10px 0;"></div>
                                <p><strong><?= htmlspecialchars($card['title']) ?></strong> (<?= htmlspecialchars($card['year']) ?>)</p>
                                <p><small><?= htmlspecialchars($card['artist']) ?> – <?= htmlspecialchars($card['platform']) ?></small></p>

                                <div class="card-actions" style="display: flex; justify-content: center; gap: 15px; margin-top: 15px; align-items: center; background: rgba(255,255,255,0.05); padding: 8px; border-radius: 8px;">
                                    
                                    <a href="<?= htmlspecialchars($card['songlink']) ?>" target="_blank" title="Song anhören" rel="noopener noreferrer">
                                        <img src="<?= ROOT_URL ?>assets/icons/music_note.svg" style="width: 24px; height: 24px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                                    </a>

                                    <a href="/cards/download?id=<?= $card['id'] ?>" title="Karte herunterladen">
                                        <img src="/assets/icons/download.svg" style="width: 24px; height: 24px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                                    </a>

                                    <?php if ($isOwner): ?>
                                        <a href="/cards/edit?id=<?= $card['id'] ?>" title="Bearbeiten">
                                            <img src="/assets/icons/edit.svg" style="width: 24px; height: 24px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                                        </a>

                                        <span class="status-toggle" 
                                            onclick="togglePublicStatus(<?= $card['id'] ?>, this)"
                                            data-status="<?= $card['is_public'] ?>"
                                            title="<?= $card['is_public'] ? 'Öffentlich sichtbar' : 'Nur für mich privat' ?>"
                                            style="cursor: pointer;">
                                            <img src="/assets/icons/<?= $card['is_public'] ? 'public.svg' : 'public_off.svg' ?>" 
                                                style="width: 24px; height: 24px; transition: transform 0.2s;" 
                                                onmouseover="this.style.transform='scale(1.2)'" 
                                                onmouseout="this.style.transform='scale(1)'">
                                        </span>

                                        <span onclick="openPlaylistModal(<?= $card['id'] ?>)" title="Zu Playlist hinzufügen" style="cursor: pointer;">
                                            <img src="/assets/icons/playlist_add.svg" style="width: 24px; height: 24px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                                        </span>

                                        <a href="/cards/delete?id=<?= $card['id'] ?>" onclick="return confirm('Möchtest du diese Karte wirklich unwiderruflich löschen?');" title="Löschen">
                                            <img src="/assets/icons/delete.svg" style="width: 24px; height: 24px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                </div>
                
                <button id="scroll-right" class="carousel-button" style="display:none;">&#8594;</button>
            </div>

            <div style="text-align: center; margin-top: 10px;">
                <button id="toggleViewBtn" class="button">Alle anzeigen (Grid)</button>
            </div>

        <?php endif; ?>
    </div>

    <?php if ($isOwner && !empty($playlists)): ?>
        <hr style="border-color: #333; margin: 40px 0;">
        <h2 class="header-style">Deine Playlists</h2>
        <section class="playlist-grid">
            <?php foreach ($playlists as $playlist): ?>
                <div class="card" style="min-height: 150px; display: flex; flex-direction: column; justify-content: center;">
                    <a href="/playlists/show?id=<?= $playlist['id'] ?>" style="font-size: 1.2rem; font-weight: bold;">
                        <?= htmlspecialchars($playlist['name']) ?>
                    </a>
                    <div style="margin-top: 15px;">
                        <a href="/playlists/show?id=<?= $playlist['id'] ?>" title="Ansehen">
                            <img src="/assets/icons/edit.svg" style="width: 20px;">
                        </a>
                        <a href="/playlists/delete?id=<?= $playlist['id'] ?>" onclick="return confirm('Playlist wirklich löschen?')" title="Löschen">
                            <img src="/assets/icons/delete.svg" style="width: 20px;">
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <?php if ($isOwner): ?>
        <hr style="border-color: #333; margin: 40px 0;">
        <h2 class="header-style">Account-Einstellungen</h2>

        <div style="max-width: 400px; margin: 0 auto;">
            
            <div class="div-style">Benutzername ändern</div>
            <form method="post" action="/account/username" class="form-container">
                <input type="text" name="new_name" placeholder="Neuer Name" value="<?= htmlspecialchars($user['name']) ?>" required class="input-field">
                <button type="submit" class="button">Ändern</button>
            </form>

            <div style="margin: 20px 0;"></div>

            <div class="div-style">E-Mail ändern</div>
            <form method="post" action="/account/email" class="form-container">
                <input type="email" name="new_email" placeholder="Neue E-Mail" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required class="input-field">
                <button type="submit" class="button">Ändern</button>
            </form>

            <div style="margin: 20px 0;"></div>

            <div class="div-style">Profilbild ändern</div>
            <form action="/profile/upload" method="post" enctype="multipart/form-data" class="form-container">
                <input type="file" name="profile_pic" accept="image/*" required class="input-field" style="border: none; padding: 10px 0;">
                <button type="submit" class="button">Hochladen</button>
            </form>

            <div style="margin: 20px 0;"></div>

            <div class="div-style">Passwort ändern</div>
            <form method="post" action="/account/password" class="form-container">
                <input type="password" name="current_password" placeholder="Aktuelles Passwort" required class="input-field">
                <input type="password" name="new_password" placeholder="Neues Passwort" required class="input-field">
                <input type="password" name="confirm_password" placeholder="Wiederholen" required class="input-field">
                <button type="submit" class="button">Ändern</button>
            </form>

            <div style="margin: 40px 0;"></div>

            <div class="div-style">Logout & Löschen</div>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <form method="post" action="/logout">
                    <button type="submit" class="button">Abmelden</button>
                </form>
                
                <form method="post" action="/account/delete" onsubmit="return confirm('ACHTUNG: Dein Account und ALLE Karten werden unwiderruflich gelöscht! Fortfahren?');">
                    <button type="submit" class="button" style="background-color: #721c24; border-color: #f5c6cb; color: white;">Account löschen</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
    <div id="playlistModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h3>Zu Playlist hinzufügen</h3>
            
            <?php if (!empty($playlists)): ?>
                <form method="POST" action="/playlists/add_card">
                    <input type="hidden" name="card_id" id="modal-card-id">
                    <label>Vorhandene Playlist:</label><br>
                    <select name="playlist_id" class="select-field" style="width: 100%; margin: 10px 0;">
                        <?php foreach ($playlists as $pl): ?>
                            <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button">Hinzufügen</button>
                </form>
                <hr style="margin: 15px 0; border-color: #444;">
            <?php endif; ?>

            <form method="POST" action="/playlists/create">
                <h4>Neue Playlist erstellen</h4>
                <input type="hidden" name="card_id" id="modal-create-card-id">
                <input type="text" name="name" placeholder="Playlist-Name" required class="input-field" style="width: 100%; margin: 10px 0;">
                <button type="submit" class="button">Erstellen & Hinzufügen</button>
            </form>
        </div>
    </div>

</main>

<script>
    // --- 1. Status Toggle (AJAX) ---
    function togglePublicStatus(cardId, element) {
        const img = element.querySelector('img');
        const currentStatus = element.getAttribute('data-status');
        const newStatus = currentStatus == '1' ? '0' : '1';

        fetch('/cards/status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `card_id=${cardId}&is_public=${newStatus}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                img.src = newStatus == '1' ? '/assets/icons/public.svg' : '/assets/icons/public_off.svg';
                element.setAttribute('data-status', newStatus);
                element.setAttribute('title', newStatus == '1' ? 'Öffentlich' : 'Privat');
            } else {
                alert('Fehler: ' + (data.message || 'Konnte Status nicht ändern'));
            }
        })
        .catch(err => console.error(err));
    }

    // --- 2. Playlist Modal ---
    function openPlaylistModal(cardId) {
        document.getElementById('playlistModal').style.display = 'flex';
        document.getElementById('modal-card-id').value = cardId;
        document.getElementById('modal-create-card-id').value = cardId;
    }

    function closeModal() {
        document.getElementById('playlistModal').style.display = 'none';
    }

    // Modal schließen bei Klick daneben
    window.onclick = function(event) {
        const modal = document.getElementById('playlistModal');
        if (event.target == modal) closeModal();
    }

    // --- 3. Carousel Logic ---
    const scrollLeftBtn = document.getElementById('scroll-left');
    const scrollRightBtn = document.getElementById('scroll-right');
    const toggleBtn = document.getElementById('toggleViewBtn');
    const carousel = document.querySelector('.card-carousel');
    const grid = document.getElementById('cardGrid');
    let isGridView = false;

    // Nur initialisieren wenn Elemente da sind
    if (scrollLeftBtn && carousel) {
        // Buttons anzeigen wenn Content breiter als Container
        if (carousel.scrollWidth > carousel.clientWidth) {
            scrollLeftBtn.style.display = 'block';
            scrollRightBtn.style.display = 'block';
        }

        scrollLeftBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: -300, behavior: 'smooth' });
        });

        scrollRightBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: 300, behavior: 'smooth' });
        });

        toggleBtn.addEventListener('click', () => {
            if (!isGridView) {
                // Zur Grid Ansicht wechseln (Umbrechen)
                grid.classList.remove('horizontal-scroll');
                grid.style.flexWrap = 'wrap';
                grid.style.justifyContent = 'center';
                toggleBtn.textContent = "Weniger anzeigen (Carousel)";
                scrollLeftBtn.style.display = 'none';
                scrollRightBtn.style.display = 'none';
            } else {
                // Zur Carousel Ansicht wechseln (Scrollbar)
                grid.classList.add('horizontal-scroll');
                grid.style.flexWrap = 'nowrap';
                grid.style.justifyContent = 'flex-start';
                toggleBtn.textContent = "Alle anzeigen (Grid)";
                scrollLeftBtn.style.display = 'block';
                scrollRightBtn.style.display = 'block';
            }
            isGridView = !isGridView;
        });
    }

    // Message ausblenden
    setTimeout(() => {
        const msg = document.getElementById('floating-message');
        if(msg) {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }
    }, 3000);

</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>