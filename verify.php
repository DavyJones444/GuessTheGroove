<?php
require 'lib/db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Token in der Tabelle suchen
    $stmt = $pdo->prepare("SELECT * FROM email_verifications WHERE token = ?");
    $stmt->execute([$token]);
    $verification = $stmt->fetch();

    if ($verification) {
        // Benutzerdaten aktualisieren
        $userId = $verification['user_id'];
        $stmt = $pdo->prepare("UPDATE users SET verified = 1 WHERE id = ?");
        $stmt->execute([$userId]);

        // Verifizierungsdatensatz löschen
        $stmt = $pdo->prepare("DELETE FROM email_verifications WHERE token = ?");
        $stmt->execute([$token]);

        header("Location: index.php?verified=1");
        exit();
        
    } else {
        echo "Ungültiger oder abgelaufener Verifizierungstoken.";
    }
} else {
    echo "Kein Token angegeben.";
}
?>
