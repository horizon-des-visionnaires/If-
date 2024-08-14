<?php

namespace adviceMeeting;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use DateTime;
use DateTimeZone;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/adviceMeetingModel.php';

class adviceMeetingController
{
    protected $twig;
    private $loader;
    private $adviceMeetingModel;
    private $notificationModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->adviceMeetingModel = new \adviceMeeting\adviceMeetingModel();
        $this->notificationModel = new \notification\notificationModel();
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

        $adviceData = $this->adviceMeetingModel->getBuyAdviceData($IdBuyAdvice);
        $adviceImages = [];
        $showSatisfactionForm = false;

        if ($adviceData) {
            if ($userId !== $adviceData['SellerId'] && $userId !== $adviceData['BuyerId']) {
                header('Location: /advice');
                exit;
            }
            $adviceImages = $this->adviceMeetingModel->getAdviceImages($adviceData['IdAdvice']);

            $timezone = new DateTimeZone('Europe/Paris');
            $currentDateTime = new DateTime('now', $timezone);
            $adviceEndDateTime = new DateTime($adviceData['BuyAdviceDate'] . ' ' . $adviceData['BuyAdviceEndTime'], $timezone);

            // Debugging output
            error_log("Current DateTime: " . $currentDateTime->format('Y-m-d H:i:s'));
            error_log("Advice End DateTime: " . $adviceEndDateTime->format('Y-m-d H:i:s'));

            if ($adviceEndDateTime <= $currentDateTime) {
                $showSatisfactionForm = true;
            }
        }

        $unreadCount = $this->notificationModel->getUnreadNotificationCount($userId);

        echo $this->twig->render('adviceMeeting/adviceMeeting.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'adviceData' => $adviceData,
            'adviceImages' => $adviceImages,
            'unreadCount' => $unreadCount,
            'showSatisfactionForm' => $showSatisfactionForm,
        ]);
    }
}
