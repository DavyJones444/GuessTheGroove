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
    public function sendMail($to, $subject, $body, $replyTo = null) {
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
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Fehler ins Server-Log schreiben, nicht auf den Bildschirm
            error_log("Mailer Fehler: {$mail->ErrorInfo}");
            return false;
        }
    }

    // Spezielle E-Mail für Verifizierung
    public function sendVerificationEmail($email, $token) {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        $verificationLink = rtrim($baseUrl, '/') . "/verify?token=$token";
        
        $subject = 'Bitte bestätige deine E-Mail-Adresse';
        $body = "Hallo,\n\nbitte klicke auf den folgenden Link, um deine E-Mail-Adresse zu bestätigen:\n$verificationLink";

        return $this->sendMail($email, $subject, $body);
    }

    // Kontaktformular E-Mail
    public function sendContactMail($fromName, $fromEmail, $message) {
        // Empfänger ist der Admin (also du selbst oder die SMTP Adresse)
        // Du kannst das auch in die .env auslagern als CONTACT_RECEIVER
        $to = "hitstercustoms@gmail.com"; 
        
        $subject = "Neue Kontaktanfrage von $fromName";
        
        // Nachricht zusammenbauen
        $body = "Neue Nachricht über das Kontaktformular:\n\n";
        $body .= "Name: $fromName\n";
        $body .= "E-Mail: $fromEmail\n\n";
        $body .= "Nachricht:\n$message";
        
        // Wir übergeben den Absender als Reply-To
        // Damit nutzt er SMTP, aber du klickst auf "Antworten" und schreibst dem Nutzer
        return $this->sendMail($to, $subject, $body, ['email' => $fromEmail, 'name' => $fromName]);
    }
}