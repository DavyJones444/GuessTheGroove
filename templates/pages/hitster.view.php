<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="wrapper">
    <div class="div-style" style="flex-direction: column; padding: 40px 20px;">
        
        <div style="font-size: 60px; margin-bottom: 20px;">🤷‍♂️</div>

        <h2 class="header-style">Karte noch nicht verknüpft</h2>
        
        <p class="text-style" style="max-width: 500px;">
            Die gescannte Hitster-Karte mit der ID 
            <strong style="color: #7da7ff;">#<?= htmlspecialchars($viewData['hitsterId']) ?></strong> 
            wurde in unserer Datenbank noch nicht mit einem Streaming-Dienst verknüpft.
        </p>

        <div style="background-color: #1c1c2b; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #333;">
            <p style="margin: 0; font-size: 0.9rem; color: #aaa;">Was möchtest du tun?</p>
        </div>

        <a href="<?= htmlspecialchars($viewData['originalUrl']) ?>" target="_blank" class="button" style="margin-bottom: 15px;">
            🚀 In offizieller Hitster-App öffnen
        </a>

        <a href="/play" class="button" style="background-color: transparent; border: 1px solid #555;">
            🔄 Andere Karte scannen
        </a>

        <?php 
        // Optional: Wenn der User ein Admin ist, zeigen wir einen "Jetzt verknüpfen" Link
        // (ID 1 und 2 waren deine Admins laut admin_tool.php)
        $userId = $_SESSION['user_id'] ?? 0;
        if (in_array($userId, [1, 2])): 
        ?>
            <div style="margin-top: 30px; border-top: 1px solid #333; padding-top: 20px; width: 100%;">
                <p style="color: #ffcc00; font-size: 0.9rem;">Admin-Option:</p>
                <a href="/admin" class="text-style" style="color: #ffcc00; text-decoration: underline;">
                    Diese ID (<?= htmlspecialchars($viewData['hitsterId']) ?>) im Admin-Tool anlegen
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>