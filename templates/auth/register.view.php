<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="wrapper">
    <h2 class="header-style">Registrieren</h2>

    <?php if (!empty($viewData['error'])): ?>
        <div class="div-style" style="background-color: #ff4d4d; color: white; padding: 10px; border-radius: 5px; max-width: 400px; margin: 10px auto;">
            <?= htmlspecialchars($viewData['error']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($viewData['message'])): ?>
        <div class="success-message" style="position: relative;">
            <?= htmlspecialchars($viewData['message']) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/register" class="form-container">
        
        <input type="text" name="name" placeholder="Benutzername" class="input-field" required 
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        
        <input type="email" name="email" placeholder="E-Mail Adresse" class="input-field" required 
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        
        <input type="password" name="password" placeholder="Passwort" class="input-field" required>
        
        <button type="submit" class="button">Registrieren</button>
    </form>

    <div class="div-style">
        <p>Bereits einen Account? <a href="/login">Hier anmelden</a></p>
    </div>
</div>

<script>
    // Erfolgsmeldung nach 5 Sekunden ausblenden
    setTimeout(function () {
        const msg = document.querySelector('.success-message');
        if (msg) {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }
    }, 5000);
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>