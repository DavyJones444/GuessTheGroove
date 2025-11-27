<main class="div-style">
    <div>
        <h1 class="header-style">Kontakt</h1>

        <?php if (!empty($viewData['success'])): ?>
            <p class="success-message"><?= htmlspecialchars($viewData['success']) ?></p>
        <?php endif; ?>

        <?php if (!empty($viewData['error'])): ?>
            <p class="floating-message"><?= htmlspecialchars($viewData['error']) ?></p>
        <?php endif; ?>

        <form class="form-container" method="post" action="/kontakt">
            <input class="input-field" type="text" name="name" placeholder="Ihr Name" 
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            
            <input class="input-field" type="email" name="email" placeholder="Ihre E-Mail" 
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            
            <textarea class="input-field" name="nachricht" rows="5" placeholder="Ihre Nachricht" required><?= htmlspecialchars($_POST['nachricht'] ?? '') ?></textarea>
            
            <button class="button" type="submit">Absenden</button>
        </form>
    </div>
</main>