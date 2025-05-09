<?php
session_start();
require 'lib/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $password = $_POST['password'];

    // Hole den Benutzer aus der Datenbank
    $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ?");
    $stmt->execute([$name]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Passwort ist korrekt, Session setzen
        $_SESSION['user_id'] = $user['id'];
        header("Location: profile");
        exit;
    } else {
        // Falscher Nutzername oder Passwort
        $error = "Login fehlgeschlagen. Bitte überprüfe deinen Benutzernamen und Passwort.";
    }
}

$title = "Login";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="de">
<body>
    <div class="wrapper">
    <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
        <p style="color:green;">Dein Passwort wurde erfolgreich geändert. Du kannst dich jetzt einloggen.</p>
    <?php endif; ?>
    <h2 class="header-style">Login</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post" class="form-container">
        Benutzername: <input type="text" name="name" required><br>
        Passwort: <input type="password" name="password" required><br>
        <button type="submit">Anmelden</button>
    </form>
    <a href="login_email">Mit E-Mail Adresse anmelden</a>
    <p><a href="forgot_password.php">Passwort vergessen?</a></p>
    <p>Noch keinen Account? <a href="register">Jetzt registrieren</a></p>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>
