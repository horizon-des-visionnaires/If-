<?php

namespace forgotPassword;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/forgotPasswordModel.php';

class forgotPasswordController
{
    protected $twig;
    private $loader;
    private $forgotPasswordModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->forgotPasswordModel = new \forgotPassword\forgotPasswordModel();
    }

    public function forgotPassword()
    {
        $error = $this->getDataForgotPassword();
        echo $this->twig->render('forgotPassword/forgotPassword.html.twig', ['error' => $error]);
    }

    public function getDataForgotPassword()
    {
        if (isset($_POST['submit'])) {
            $email = $_POST['email'];
            $token = bin2hex(random_bytes(16));

            if ($this->forgotPasswordModel->storeTempForgotPasswordData($email, $token)) {
                $this->sendResetPasswordEmail($email, $token);
                header("Location: /reset-password");
                exit;
            } else {
                return 'Erreur lors de l\'envoie du mail';
            }
        }
    }

    public function sendResetPasswordEmail($email, $token)
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
            $mail->Body    = "Voici votre token de vérification : <b>$token</b>. Utilisez-le pour vérifier réinitialiser votre mot de passe.";

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}
