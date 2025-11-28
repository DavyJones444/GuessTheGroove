<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="wrapper">
    <h2 class="header-style">Hitster Admin Tool</h2>

    <?php if (!empty($viewData['message'])): ?>
        <div class="success-message" style="position: relative; margin-bottom: 20px;">
            <?= htmlspecialchars($viewData['message']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($viewData['error'])): ?>
        <div class="div-style" style="background-color: #ff4d4d; color: white; padding: 10px; border-radius: 5px;">
            <?= htmlspecialchars($viewData['error']) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/admin/store" class="form-container" style="max-width: 500px; margin: 0 auto;">
        <div style="width: 100%; margin-bottom: 15px;">
            <label style="color:white;">Hitster-Karten-ID:</label>
            <input type="text" name="hitster_id" class="input-field" style="width: 100%;" required>
        </div>

        <div style="width: 100%; margin-bottom: 15px;">
            <label style="color:white;">Song-URL:</label>
            <input type="url" name="song_url" class="input-field" style="width: 100%;" required>
        </div>

        <button type="submit" class="button">Zuordnung speichern</button>
    </form>

    <hr style="border-color: #333; margin: 40px 0;">

    <h3 class="header-style">Vorhandene Zuordnungen</h3>
    
    <div style="overflow-x: auto; padding: 0 20px;">
        <table style="width: 100%; border-collapse: collapse; color: white; min-width: 600px;">
            <thead>
                <tr style="background-color: #1c1c2b; text-align: left;">
                    <th style="padding: 10px; border-bottom: 2px solid #333;">ID</th>
                    <th style="padding: 10px; border-bottom: 2px solid #333;">Hitster-ID</th>
                    <th style="padding: 10px; border-bottom: 2px solid #333;">Song-URL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($viewData['mappings'] as $row): ?>
                    <tr style="border-bottom: 1px solid #333;">
                        <td style="padding: 10px;"><?= htmlspecialchars($row['id']) ?></td>
                        <td style="padding: 10px; color: #7da7ff; font-weight: bold;"><?= htmlspecialchars($row['hitster_id']) ?></td>
                        <td style="padding: 10px;">
                            <a href="<?= htmlspecialchars($row['song_url']) ?>" target="_blank" style="word-break: break-all;">
                                <?= htmlspecialchars($row['song_url']) ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>