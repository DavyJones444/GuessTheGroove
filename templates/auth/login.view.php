<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-wrapper">
    <div class="auth-card">
        <img src="<?= ROOT_URL ?>assets/images/logo_solo.png" alt="Logo" class="auth-logo">
        
        <h2 class="auth-title">Willkommen zurück</h2>
        <p class="auth-subtitle">Melde dich an, um weiterzumachen</p>

        <?php if (!empty($viewData['error'])): ?>
            <div class="auth-alert error">
                ⚠️ <?= htmlspecialchars($viewData['error']) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
            <div class="auth-alert success">
                Passwort erfolgreich geändert! Bitte einloggen.
            </div>
        <?php endif; ?>

        <form method="post" action="/login" class="auth-form">
            <div>
                <input type="text" name="name" placeholder="Benutzername oder E-Mail" class="auth-input" required autofocus>
            </div>
            <div>
                <input type="password" name="password" placeholder="Passwort" class="auth-input" required>
            </div>
            
            <button type="submit" class="button auth-button">Anmelden</button>
        </form>
        
        <div class="auth-footer">
            <a href="/login/code">Probleme? Mit E-Mail-Code anmelden</a>
            <a href="/forgot-password">Passwort vergessen?</a>
            <hr style="width: 100%; border-color: #333; margin: 10px 0;">
            <span>Noch keinen Account? <a href="/register">Registrieren</a></span>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>