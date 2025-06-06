<?php 
require __DIR__ . '/lib/db.php';
session_start();
include 'header.php'; ?>
<?php require_once 'lib/mailer.php'; ?>

<main class="div-style">
    <div>
        <h1 class="header-style">Kontakt</h1>

        <?php
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $name = htmlspecialchars(trim($_POST["name"] ?? ''));
            $email = filter_var(trim($_POST["email"] ?? ''), FILTER_VALIDATE_EMAIL);
            $nachricht = htmlspecialchars(trim($_POST["nachricht"] ?? ''));

            if ($name && $email && $nachricht) {
                $to = "hitstercustoms@gmail.com"; // Empfängeradresse
                $subject = "Neue Kontaktanfrage von $name";
                $body = "Name: $name\nE-Mail: $email\n\nNachricht:\n$nachricht";

                sendMail($to, $subject, $body);

                echo '<p class="success-message">Vielen Dank! Ihre Nachricht wurde gesendet.</p>';
            } else {
                echo '<p class="floating-message">Bitte füllen Sie alle Felder korrekt aus.</p>';
            }
        }
        ?>

        <form class="form-container" method="post" action="">
            <input class="input-field" type="text" name="name" placeholder="Ihr Name" required>
            <input class="input-field" type="email" name="email" placeholder="Ihre E-Mail" required>
            <textarea class="input-field" name="nachricht" rows="5" placeholder="Ihre Nachricht" required></textarea>
            <button class="button" type="submit">Absenden</button>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>
