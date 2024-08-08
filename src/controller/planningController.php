<?php

namespace planning;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/planningModel.php';

class planningController
{
    protected $twig;
    private $loader;
    private $planningModel;
    private $notificationModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->planningModel = new \planning\planningModel();
        $this->notificationModel = new \notification\notificationModel();
    }

    public function planning()
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

        $unreadCount = $this->notificationModel->getUnreadNotificationCount($userId);
        $adviceData = $this->planningModel->getBuyAdviceData($userId);
        $adviceImages = [];
        $adviceImages = $this->planningModel->getAdviceImages($adviceData['IdAdvice']);
        $userInfo = $this->planningModel->getUserInfo($userId);

        echo $this->twig->render('planning/planning.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'unreadCount' => $unreadCount,
            'adviceData' => $adviceData,
            'adviceImages' => $adviceImages,
            'userFirstName' => $userInfo['FirstName'],
            'userLastName' => $userInfo['LastName']
        ]);
    }
}
