<?php
// src/Controller/AuthController.php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Model/UserRepository.php';
require_once __DIR__ . '/../Service/MailerService.php';

class AuthController extends BaseController {
    
    private $userRepo;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->userRepo = new UserRepository($pdo);
    }

    // --- LOGIN ---

    public function login() {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = $_POST['name'] ?? $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Debugging 1: Was kommt an?
            // var_dump($identifier, $password); die(); 

            $user = filter_var($identifier, FILTER_VALIDATE_EMAIL) 
                ? $this->userRepo->findByEmail($identifier) 
                : $this->userRepo->findByName($identifier);

            // Debugging 2: User gefunden?
            // var_dump($user); die();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                
                // Debugging 3: Session gesetzt?
                //var_dump($_SESSION); die("Session gesetzt! Weiterleitung...");
                header("Location: /profile");
                exit;
            } else {
                $error = "Login fehlgeschlagen. Daten überprüfen.";
                // Debugging 4: Warum fehlgeschlagen?
                // var_dump("Passwort falsch oder User null");
            }
        }
        $this->render('auth/login.view.php', ['title' => 'Login', 'error' => $error]);
    }

    public function loginWithCode() {
        $error = null;
        $step = isset($_POST['code']) ? 2 : 1; // Schritt 1: Email eingeben, Schritt 2: Code eingeben

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($step === 1) { // Code anfordern
                $email = trim($_POST['email']);
                $user = $this->userRepo->findByEmail($email);
                
                if ($user) {
                    $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                    $this->userRepo->createLoginCode($user['id'], $code);
                    
                    $mailer = new MailerService();
                    $mailer->sendMail($email, "Dein Login-Code", "Code: $code");
                    
                    // Wir merken uns die Email für Schritt 2 in der Session oder View
                    $this->render('auth/login_code.view.php', ['title' => 'Code eingeben', 'step' => 2, 'email' => $email]);
                    return;
                } else {
                    $error = "Email nicht gefunden.";
                }
            } elseif ($step === 2) { // Code prüfen
                $email = $_POST['email'];
                $code = $_POST['code'];
                $user = $this->userRepo->findByEmail($email);

                if ($user && $this->userRepo->verifyLoginCode($user['id'], $code)) {
                    $_SESSION['user_id'] = $user['id'];
                    $this->userRepo->deleteLoginCode($user['id']); // Code verbrauchen
                    header("Location: /profile");
                    exit;
                } else {
                    $error = "Code ungültig.";
                    $this->render('auth/login_code.view.php', ['title' => 'Code eingeben', 'step' => 2, 'email' => $email, 'error' => $error]);
                    return;
                }
            }
        }
        $this->render('auth/login_code.view.php', ['title' => 'Login mit Code', 'step' => 1, 'error' => $error]);
    }

    public function logout() {
        session_destroy();
        header("Location: /");
        exit;
    }

    // --- REGISTER ---

    public function register() {
        $error = null;
        $message = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            // Check ob existiert
            if ($this->userRepo->findByEmail($email) || $this->userRepo->findByName($name)) {
                $error = "Name oder E-Mail bereits vergeben.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(16));
                
                $this->userRepo->create($name, $email, $hash, $token);
                
                $mailer = new MailerService();
                $mailer->sendVerificationEmail($name, $email, $token);
                
                $message = "Registrierung erfolgreich! Bitte E-Mail bestätigen.";
            }
        }
        $this->render('auth/register.view.php', ['title' => 'Registrieren', 'error' => $error, 'message' => $message]);
    }

    // --- PASSWORT VERGESSEN ---

    public function forgotPassword() {
        $message = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $user = $this->userRepo->findByEmail($email);

            if ($user) {
                $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                $this->userRepo->createLoginCode($user['id'], $code);
                
                (new MailerService())->sendMail($email, "Passwort zurücksetzen", "Dein Code: $code");
                $message = "Code gesendet.";
                // Weiterleitung zum Reset-Formular
                header("Location: /reset-password?email=" . urlencode($email));
                exit;
            } else {
                $error = "Email nicht gefunden.";
            }
        }
        $this->render('auth/forgot_password.view.php', ['title' => 'Passwort vergessen', 'error' => $error]);
    }

    public function resetPassword() {
        $error = null;
        $email = $_GET['email'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $code = $_POST['code'];
            $newPw = $_POST['new_password'];
            
            $user = $this->userRepo->findByEmail($email);
            
            if ($user && $this->userRepo->verifyLoginCode($user['id'], $code)) {
                $hash = password_hash($newPw, PASSWORD_DEFAULT);
                $this->userRepo->updatePassword($user['id'], $hash);
                $this->userRepo->deleteLoginCode($user['id']);
                
                header("Location: /login?reset=success");
                exit;
            } else {
                $error = "Code ungültig.";
            }
        }
        $this->render('auth/reset_password.view.php', ['title' => 'Neues Passwort', 'email' => $email, 'error' => $error]);
    }

    public function verifyEmail() {
        $token = $_GET['token'] ?? null;

        if (!$token) {
            die("Kein Token angegeben."); // Oder schöne Fehlerseite
        }

        // Token suchen
        $stmt = $this->pdo->prepare("SELECT * FROM email_verifications WHERE token = ?");
        $stmt->execute([$token]);
        $verification = $stmt->fetch();

        if ($verification) {
            // User aktivieren
            $userId = $verification['user_id'];
            $stmt = $this->pdo->prepare("UPDATE users SET verified = 1 WHERE id = ?");
            $stmt->execute([$userId]);

            // Token löschen
            $stmt = $this->pdo->prepare("DELETE FROM email_verifications WHERE token = ?");
            $stmt->execute([$token]);

            // Redirect zur Startseite mit Erfolgsmeldung
            header("Location: /?verified=1");
            exit();
        } else {
            // Fehler anzeigen (Du könntest hier auch eine View rendern)
            die("Ungültiger oder abgelaufener Verifizierungstoken.");
        }
    }
}