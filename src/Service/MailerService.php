<?php

// Falls du PHPMailer via Composer nutzt:
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService {
    
    public function sendContactMail($fromName, $fromEmail, $message) {
        // Hier deine Mail-Logik (entweder mail() oder PHPMailer)
        // Beispiel für simple mail() Funktion wie vorher:
        
        $to = "hitstercustoms@gmail.com";
        $subject = "Neue Kontaktanfrage von $fromName";
        $body = "Name: $fromName\nE-Mail: $fromEmail\n\nNachricht:\n$message";
        
        // Header bauen
        $headers = "From: " . $fromEmail . "\r\n" .
                   "Reply-To: " . $fromEmail . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        // Senden
        return mail($to, $subject, $body, $headers);
    }
}