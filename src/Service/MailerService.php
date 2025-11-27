<?php
// src/Service/MailerService.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService {
    
    /**
     * Zentraler Mail-Versand via SMTP
     * * @param string $to Empfänger
     * @param string $subject Betreff
     * @param string $body Inhalt
     * @param array|null $replyTo Optional: ['email' => '...', 'name' => '...']
     */
    private function renderTemplate($subject, $headline, $content, $buttonUrl = null, $buttonText = null) {
        // Output Buffering starten, um die Datei in eine Variable zu speichern
        ob_start();
        
        // Diese Variablen sind im Template verfügbar:
        // $subject, $headline, $content, $buttonUrl, $buttonText (aus den Parametern)
        
        // Template laden
        require __DIR__ . '/../../templates/mail/default.php';
        
        // Inhalt des Buffers zurückgeben und Buffer löschen
        return ob_get_clean();
    }

    public function sendMail($to, $subject, $bodyHtml, $altBodyText, $replyTo = null) {
        $mail = new PHPMailer(true);

        try {
            // Server Einstellungen
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'];
            $mail->Password   = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['SMTP_PORT'];
            $mail->CharSet    = 'UTF-8'; 

            // Absender ist IMMER dein System (sonst Spam-Gefahr)
            $mail->setFrom($_ENV['SMTP_USER'], $_ENV['SMTP_FROM_NAME']);
            
            // Empfänger hinzufügen
            $mail->addAddress($to);

            // WICHTIG: Wenn es eine Kontaktanfrage ist, setzen wir Reply-To
            // So antwortest du direkt dem Nutzer, obwohl die Mail technisch von dir kommt.
            if ($replyTo) {
                $mail->addReplyTo($replyTo['email'], $replyTo['name']);
            }

            // Inhalt
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $bodyHtml;      // Das schöne HTML Design
            $mail->AltBody = $altBodyText;   // Fallback für Text-only Clients

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Fehler ins Server-Log schreiben, nicht auf den Bildschirm
            error_log("Mailer Fehler: {$mail->ErrorInfo}");
            return false;
        }
    }

    // Spezielle E-Mail für Verifizierung
    public function sendVerificationEmail($name, $email, $token) {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        $verificationLink = rtrim($baseUrl, '/') . "/verify?token=$token";
        
        $subject = 'E-Mail Bestätigung';
        $headline = 'Willkommen bei Guess The Groove!';
        $content = "Hallo $name,\n\nschön, dass du dabei bist! Bitte bestätige deine E-Mail-Adresse, um dein Konto vollständig zu aktivieren.";
        
        // 1. HTML generieren
        $htmlBody = $this->renderTemplate($subject, $headline, $content, $verificationLink, 'E-Mail bestätigen');
        
        // 2. Text-Version generieren (für alte Clients)
        $textBody = "$headline\n\n$content\n\nLink: $verificationLink";

        return $this->sendMail($email, $subject, $htmlBody, $textBody);
    }

    // Kontaktformular E-Mail
    public function sendContactMail($fromName, $fromEmail, $message) {
        $to = "hitstercustoms@gmail.com"; // Oder $_ENV['ADMIN_EMAIL']
        $subject = "Kontaktanfrage von $fromName";
        $headline = "Neue Nachricht";
        
        $content = "Du hast eine neue Nachricht über das Kontaktformular erhalten.\n\n" . 
                   "<strong>Name:</strong> $fromName\n" . 
                   "<strong>E-Mail:</strong> $fromEmail\n\n" . 
                   "<strong>Nachricht:</strong>\n" . nl2br(htmlspecialchars($message));

        // Hier kein Button nötig
        $htmlBody = $this->renderTemplate($subject, $headline, $content);
        
        $textBody = "Neue Nachricht von $fromName ($fromEmail):\n\n$message";

        return $this->sendMail($to, $subject, $htmlBody, $textBody, ['email' => $fromEmail, 'name' => $fromName]);
    }
}