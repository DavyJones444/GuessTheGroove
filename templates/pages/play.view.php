<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="wrapper">
    <h2 class="header-style">Musikquiz Player</h2>

    <form method="get" id="urlForm" style="display: none;">
        <input type="text" name="url" value="<?= htmlspecialchars($viewData['songUrl']) ?>" >
        <button type="submit">Laden</button>
    </form>

    <div class="div-style">
        <button id="scanBtn" class="button" onclick="startScanning()">Scan QR Code</button>
    </div>

    <div id="player-container"></div>
    
    </div>

<script>
    // Wir nutzen hier die PHP Variablen, die der Controller übergeben hat
    const service = "<?= $viewData['service'] ?>";
    const songUrl = "<?= $viewData['songUrl'] ?>";
    const token = "<?= $viewData['token'] ?>";
    
    // ... Dein gesamter JavaScript Code (Scanner, Player) ...
    // ... Du kannst den JS Code auch in public/js/player.js auslagern und hier nur einbinden! ...
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>