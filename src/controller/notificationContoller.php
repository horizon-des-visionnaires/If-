<?php

namespace notification;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/notificationModel.php';

class notificationController
{
    protected $twig;
    private $loader;
    private $notificationModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->notificationModel = new \notification\notificationModel();
    }

    public function notification()
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

        $notifications = $this->notificationModel->getUserNotifications($userId);
        $unreadCount = $this->notificationModel->getUnreadNotificationCount($userId);
        $this->markAsRead();

        $userInfo = $this->notificationModel->getUserInfo($userId);

        $this->notificationModel->deleteExpiredNotifications();

        echo $this->twig->render('notification/notification.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'userFirstName' => $userInfo['FirstName'],
            'userLastName' => $userInfo['LastName']
        ]);
    }

    public function markAsRead()
    {
        if (isset($_POST['IdNotification'])) {
            $notificationId = $_POST['IdNotification'];
            $this->notificationModel->markNotificationAsRead($notificationId);
        }
    }
}
