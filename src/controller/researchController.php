<?php

namespace research;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/researchModel.php';

class researchContoller
{
    protected $twig;
    private $loader;
    private $researchModel;
    private $notificationModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->researchModel = new \research\researchModel();
        $this->notificationModel = new \notification\notificationModel();
    }

    public function research()
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

        $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
        $userData = $this->researchModel->getFilteredProUsers($searchQuery);
        $unreadCount = $this->notificationModel->getUnreadNotificationCount($userId);

        echo $this->twig->render('research/research.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'userData' => $userData,
            'searchQuery' => $searchQuery,
            'unreadCount' => $unreadCount
        ]);
    }
}
