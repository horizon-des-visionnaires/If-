<?php

namespace register;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/registerModel.php';

class registerController
{
    protected $twig;
    private $loader;
    private $registerModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->registerModel = new \register\registerModel();
    }

    public function register()
    {
        $error = $this->getRegisterData();
        echo $this->twig->render('register/register.html.twig', ['error' => $error]);
    }

    public function getRegisterData()
    {
        if (isset($_POST['submit'])) {
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $email = $_POST['email'];
            $userPassword = $_POST['userPassword'];

            if (strlen($userPassword) < 8) {
                return "Le mot de passe doit contenir au moins 8 caractères.";
            }

            if (!preg_match('/[A-Z]/', $userPassword) || !preg_match('/[a-z]/', $userPassword) || !preg_match('/[0-9]/', $userPassword)) {
                return "Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule et un chiffre.";
            }

            $hashed_password = password_hash($userPassword, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(16));

            if ($this->registerModel->storeTempRegisterData($firstName, $lastName, $email, $hashed_password, $token)) {
                $this->sendVerificationEmail($email, $token);
                header("Location: /verify");
                exit;
            } else {
                return 'Erreur lors de l\'inscription';
            }
        }

        return null;
    }

    private function sendVerificationEmail($email, $token)
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = 'rebouletlucas@gmail.com';
            $mail->Password = 'uqnb dmnm xshk aadp';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('no-reply@ifa.com', 'Mailer');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $mail->Body    = "Voici votre token de vérification : <b>$token</b>. Utilisez-le pour vérifier votre adresse e-mail.";

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}
