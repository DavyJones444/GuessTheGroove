<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="wrapper">
    <h2 class="header-style">Login</h2>
    <?php if (!empty($viewData['error'])): ?>
        <p style="color:red;"><?= htmlspecialchars($viewData['error']) ?></p>
    <?php endif; ?>
    
    <form method="post" action="/login" class="form-container">
        Benutzername oder E-Mail: <input type="text" name="name" required><br>
        Passwort: <input type="password" name="password" required><br>
        <button type="submit">Anmelden</button>
    </form>
    
    <a href="/login/code">Mit E-Mail Code anmelden</a>
    <p><a href="/forgot-password">Passwort vergessen?</a></p>
    <p>Noch keinen Account? <a href="/register">Jetzt registrieren</a></p>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>