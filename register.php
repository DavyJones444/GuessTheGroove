<?php
require 'lib/db.php';
require 'lib/mailer.php';
session_start();

// ✅ Weiterleitung, falls schon eingeloggt
if (isset($_SESSION['user_id'])) {
    header("Location: welcome");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $name = trim($_POST['name']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prüfen, ob E-Mail oder Name bereits existieren
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR name = ?");
    $stmt->execute([$email, $name]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $error = "Benutzername oder E-Mail-Adresse ist bereits vergeben.";
    } else {
        try {
            // Generiere einen zufälligen Token für die Verifizierung
            $token = bin2hex(random_bytes(16));

            // Nutzer einfügen
            $stmt = $pdo->prepare("INSERT INTO users (email, password, name, profile_pic, verified) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([$email, $password, $name, 'default_profile.png']);
            
            // Hole neue User-ID
            $userId = $pdo->lastInsertId();

            // Token in Verifikationstabelle speichern
            $stmt = $pdo->prepare("INSERT INTO email_verifications (user_id, token) VALUES (?, ?)");
            $stmt->execute([$userId, $token]);

            // Bestätigungsmail senden
            sendVerificationEmail($email, $token);

            $message = "Registrierung erfolgreich. Bitte E-Mail bestätigen.";
        } catch (PDOException $e) {
            $error = "Fehler: " . $e->getMessage(); // Optional: Für produktiv entfernen
        }
    }
}
$title = "Registrieren";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="de">
<body>
<h2>Registrieren</h2>
<?php if (isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post" class="form-container">
    Benutzername: <input type="text" name="name" required><br>
    E-Mail: <input type="email" name="email" required><br>
    Passwort: <input type="password" name="password" required><br>
    <button type="submit">Registrieren</button>
</form>
<p>Schon registriert? <a href="login.php">Jetzt anmelden</a></p>
</body>
</html>
<?php include 'footer.php'; ?>
