<?php

namespace advice;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/adviceModel.php';

class adviceController
{
    protected $twig;
    private $loader;
    private $adviceModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->adviceModel = new \advice\adviceModel();
    }

    public function advice()
    {
        session_start();

        $isConnected = false;
        $userId = null;
        if (isset($_SESSION['IdUser'])) {
            $isConnected = true;
            $userId = $_SESSION['IdUser'];
        }

        $IsAdmin = false;
        if (isset($_SESSION['IsAdmin']) && $_SESSION['IsAdmin'] == 1) {
            $IsAdmin = true;
        }
        
        $this->getAdviceData();

        echo $this->twig->render('advice/advice.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
        ]);
    }

    public function getAdviceData()
    {
        if (isset($_POST['addAdvice'])) {
            $AdviceType = $_POST['AdviceType'];
            $AdviceDescription = $_POST['AdviceDescription'];
            $IdUser = $_SESSION['IdUser'];

            $this->adviceModel->insertAdviceData($AdviceType, $AdviceDescription, $IdUser);
        }
    }
}
