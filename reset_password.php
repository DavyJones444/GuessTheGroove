<?php
require 'lib/db.php';
session_start();

$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $code = trim($_POST['code']);
    $newPassword = $_POST['new_password'];

    // Nutzer-ID abrufen
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $userId = $user['id'];

        // Prüfen, ob Code gültig ist
        $stmt = $pdo->prepare("SELECT * FROM login_codes WHERE user_id = ? AND code = ? AND created_at >= NOW() - INTERVAL 15 MINUTE ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$userId, $code]);
        $valid = $stmt->fetch();

        if ($valid) {
            // Passwort ändern
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $userId]);

            // (Optional) Code löschen
            $pdo->prepare("DELETE FROM login_codes WHERE user_id = ?")->execute([$userId]);

            header("Location: login.php?reset=success");
            exit;

        } else {
            $error = "Ungültiger oder abgelaufener Code.";
        }
    } else {
        $error = "Kein Benutzer mit dieser E-Mail gefunden.";
    }
}
$title = "Passwort zurücksetzen";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="de">
<h2 class="form-container">Passwort zurücksetzen</h2>
<?php if ($message) echo "<p style='color:green;'>$message</p>"; ?>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="post" class="form-container">
    <label>E-Mail:</label><br>
    <input type="email" name="email" required><br>

    <label>Bestätigungscode (4-stellig):</label><br>
    <input type="text" name="code" pattern="\d{4}" required><br>

    <label>Neues Passwort:</label><br>
    <input type="password" name="new_password" required><br>

    <button type="submit">Passwort ändern</button>
</form>
</body>
</html>
<?php include 'footer.php'; ?>
