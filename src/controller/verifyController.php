<?php

namespace verify;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';
require_once __DIR__ . '/../model/verifyModel.php';

class verifyController
{
    protected $twig;
    private $loader;
    private $verifyModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->verifyModel = new \verify\verifyModel();
    }

    public function verify()
    {
        $error = null;
        if (isset($_GET['token'])) {
            $token = $_GET['token'];
            if ($this->verifyModel->verifyToken($token)) {
                header("Location: /login");
                exit;
            } else {
                $error = "Token de vérification invalide ou expiré.";
            }
        }
        echo $this->twig->render('verify/verify.html.twig', ['error' => $error]);
    }
}
