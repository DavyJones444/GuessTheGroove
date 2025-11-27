<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Service/MailerService.php';

class PageController extends BaseController {

    public function impressum() {
        $this->render('pages/impressum.view.php', ['title' => 'Impressum']);
    }

    public function privacy() {
        $this->render('pages/privacy.view.php', ['title' => 'Datenschutz']);
    }

    public function contact() {
        $success = null;
        $error = null;

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $name = htmlspecialchars(trim($_POST["name"] ?? ''));
            $email = filter_var(trim($_POST["email"] ?? ''), FILTER_VALIDATE_EMAIL);
            $nachricht = htmlspecialchars(trim($_POST["nachricht"] ?? ''));

            if ($name && $email && $nachricht) {
                // Mailer Service nutzen
                $mailer = new MailerService();
                if ($mailer->sendContactMail($name, $email, $nachricht)) {
                    $success = "Vielen Dank! Ihre Nachricht wurde gesendet.";
                    // Post-Daten leeren damit das Formular leer ist
                    $_POST = []; 
                } else {
                    $error = "Fehler beim Senden der E-Mail.";
                }
            } else {
                $error = "Bitte füllen Sie alle Felder korrekt aus.";
            }
        }

        $this->render('pages/contact.view.php', [
            'title' => 'Kontakt',
            'success' => $success,
            'error' => $error
        ]);
    }
}