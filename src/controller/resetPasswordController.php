<?php

namespace resetPassword;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';
require_once __DIR__ . '/../model/resetPasswordModel.php';

class resetPasswordController
{
    protected $twig;
    private $loader;
    private $resetPasswordModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->resetPasswordModel = new \resetPassword\resetPasswordModel();
    }

    public function resetPassword()
    {
        $error = $this->getResetPasswordData();
        echo $this->twig->render('resetPassword/resetPassword.html.twig', ['error' => $error]);
    }

    public function getResetPasswordData()
    {
        if (isset($_POST['submit'])) {
            $token = $_POST['token'];
            $newPassword = $_POST['newPassword'];

            if (strlen($newPassword) < 8) {
                return "Le mot de passe doit contenir au moins 8 caractères.";
            }

            if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
                return "Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule et un chiffre.";
            }

            $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

            $this->resetPasswordModel->resetPassword($token, $hashed_password);
        }
    }
}
