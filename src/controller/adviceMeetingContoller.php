<?php

namespace adviceMeeting;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/adviceMeetingModel.php';

class adviceMeetingController
{
    protected $twig;
    private $loader;
    private $adviceMeetingModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->adviceMeetingModel = new adviceMeetingModel();
    }

    public function adviceMeeting($IdBuyAdvice)
    {
        session_start();

        if (isset($_SESSION['IdUser'])) {
            $isConnected = true;
            $userId = $_SESSION['IdUser'];
        } else {
            $isConnected = false;
            $userId = null;
        }

        $IsAdmin = false;
        if (isset($_SESSION['IsAdmin']) && $_SESSION['IsAdmin'] == 1) {
            $IsAdmin = true;
        }

        echo $this->twig->render('adviceMeeting/adviceMeeting.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin
        ]);
    }
}
