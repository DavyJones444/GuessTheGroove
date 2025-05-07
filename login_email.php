<?php
session_start();
require 'lib/db.php';

// ✅ Weiterleitung, falls schon eingeloggt
if (isset($_SESSION['user_id'])) {
    header("Location: welcome");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hole den Benutzer aus der Datenbank
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Passwort ist korrekt, Session setzen
        $_SESSION['user_id'] = $user['id'];
        header("Location: profile");
        exit;
    } else {
        // Falsche E-Mail oder Passwort
        $error = "Login fehlgeschlagen. Bitte überprüfe deine E-Mail Adresse und Passwort.";
    }
}

$title = "Login";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="de">
<body>
<div class="wrapper">
<h2>Login</h2>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post" class="form-container">
    E-Mail Adresse: <input type="email" name="email" required><br>
    Passwort: <input type="password" name="password" required><br>
    <button type="submit">Anmelden</button>
</form>
<a href="login">Mit Benutzername anmelden</a>
<p>Noch keinen Account? <a href="register">Jetzt registrieren</a></p>
</div>
</body>
</html>
<?php include 'footer.php'; ?>
