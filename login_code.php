<?php
require 'lib/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $code = trim($_POST['code']);

    // Nutzer-ID holen
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $userId = $user['id'];

        // Code validieren (z.B. nur die letzten 15 Minuten gültig)
        $stmt = $pdo->prepare("SELECT * FROM login_codes WHERE user_id = ? AND code = ? AND created_at >= NOW() - INTERVAL 15 MINUTE ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$userId, $code]);
        $match = $stmt->fetch();

        if ($match) {
            // Erfolgreich eingeloggt
            $_SESSION['user_id'] = $userId;
            header("Location: profile.php");
            exit();
        } else {
            $error = "Ungültiger oder abgelaufener Code.";
        }
    } else {
        $error = "Kein Konto mit dieser E-Mail gefunden.";
    }
}

$title = "Mit Code einloggen";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="de">
<body>
<div class="wrapper">
<h2>Login mit Code</h2>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="post" class="form-container">
    <label>E-Mail:</label><br>
    <input type="email" name="email" required><br>
    <label>4-stelliger Code:</label><br>
    <input type="text" name="code" pattern="\d{4}" required><br>
    <button type="submit">Einloggen</button>
</form>
</div>
</body>
</html>
<?php include 'footer.php'; ?>
