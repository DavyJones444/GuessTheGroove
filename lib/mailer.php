<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // Stelle sicher, dass du PHPMailer installiert hast.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
/**
 * Versendet eine allgemeine E-Mail
 */
function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

        $mail->setFrom($_ENV['SMTP_USER'], $_ENV['SMTP_FROM_NAME']);
        $mail->addAddress($to);

        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        echo "Fehler beim Senden der E-Mail: {$mail->ErrorInfo}";
    }
}


/**
 * Spezielle E-Mail für Verifizierung
 */
function sendVerificationEmail($email, $token) {
    $verificationLink = "http://192.168.0.61/lib/verify.php?token=$token";
    $subject = 'Bitte bestätige deine E-Mail-Adresse';
    $body = "Hallo,\n\nbitte klicke auf den folgenden Link, um deine E-Mail-Adresse zu bestätigen:\n$verificationLink";

    sendMail($email, $subject, $body);
}
?>
