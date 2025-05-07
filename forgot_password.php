<?php
require 'lib/db.php';
require 'lib/mailer.php';

$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Benutzer mit dieser E-Mail suchen
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT); // 4-stelliger Code
        $userId = $user['id'];

        // Speichere den Code temporär in einer neuen Tabelle
        $stmt = $pdo->prepare("INSERT INTO login_codes (user_id, code, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$userId, $code]);

        // Code per Mail senden
        $subject = "Dein Login-Code";
        $body = "Dein Login-Code lautet: $code";
        sendMail($email, $subject, $body);

        $message = "Ein Code wurde an deine E-Mail-Adresse gesendet.";
    } else {
        $error = "Kein Konto mit dieser E-Mail gefunden.";
    }
}
$title = "Passwort vergessen";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="de">
<body>
<h2 class="header-style">Passwort vergessen</h2>
<?php if ($message) echo "<p style='color:green;'>$message</p>"; ?>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="post" class="form-container">
    <label>E-Mail-Adresse:</label><br>
    <input type="email" name="email" required><br>
    <button type="submit">Code anfordern</button>
</form>
<p>Code schon erhalten? <a href="reset_password.php">Passwort zurücksetzen</a></p>

</body>
</html>
<?php include 'footer.php'; ?>
