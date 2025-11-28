<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-wrapper">
    <div class="auth-card">
        <img src="<?= ROOT_URL ?>assets/images/logo_solo.png" alt="Logo" class="auth-logo">
        
        <h2 class="auth-title">Konto erstellen</h2>
        <p class="auth-subtitle">Werde Teil der Community</p>

        <?php if (!empty($viewData['error'])): ?>
            <div class="auth-alert error">
                <?= htmlspecialchars($viewData['error']) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($viewData['message'])): ?>
            <div class="auth-alert success">
                <?= htmlspecialchars($viewData['message']) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/register" class="auth-form">
            
            <input type="text" name="name" placeholder="Benutzername" class="auth-input" required 
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            
            <input type="email" name="email" placeholder="E-Mail Adresse" class="auth-input" required 
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            
            <input type="password" name="password" placeholder="Passwort wählen" class="auth-input" required>
            
            <button type="submit" class="button auth-button">Registrieren</button>
        </form>

        <div class="auth-footer">
            <span>Bereits einen Account? <a href="/login">Hier anmelden</a></span>
        </div>
    </div>
</div>

<script>
    setTimeout(function () {
        const msg = document.querySelector('.auth-alert.success');
        if (msg) {
            msg.style.opacity = '0';
            msg.style.transition = 'opacity 0.5s';
            setTimeout(() => msg.remove(), 500);
        }
    }, 5000);
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>