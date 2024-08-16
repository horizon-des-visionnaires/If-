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

        $this->getDataIsSatisfactory();
        $this->getDataAddNotations();
        $this->getDataRequestForRefund();

        echo $this->twig->render('adviceMeeting/adviceMeeting.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'adviceData' => $adviceData,
            'adviceImages' => $adviceImages,
            'unreadCount' => $unreadCount,
            'showSatisfactionForm' => $showSatisfactionForm,
            'roomName' => 'adviceMeeting-' . $IdBuyAdvice
        ]);
    }

    public function getDataIsSatisfactory()
    {
        if (isset($_POST['isSatisfactoryAdvice'])) {
            $idBuyAdvice = $_POST['idBuyAdvice'];
            $satisfaction = $_POST['satisfaction'];

            if ($this->adviceMeetingModel->updateAdviceValidity($idBuyAdvice, $satisfaction)) {
                // Redirect to avoid resubmission on refresh
                header('Location: /adviceMeeting-' . $idBuyAdvice);
                exit;
            } else {
                echo "Error updating advice validity.";
            }
        }
    }

    public function getDataAddNotations()
    {
        if (isset($_POST['addNotations'])) {
            $IdUserIsPro = $_POST['IdUserIsPro'];
            $IdUser = $_POST['IdUser'];
            $Note = $_POST['Note'];
            $CommentNote = $_POST['CommentNote'];
            $IdBuyAdvice = $_POST['IdBuyAdvice'];

            if ($this->adviceMeetingModel->insertNotations($IdUserIsPro, $IdUser, $Note, $CommentNote)) {
                header('Location: /adviceMeeting-' . $IdBuyAdvice);
                exit;
            } else {
                echo "Error add notations.";
            }
        }
    }

    public function getDataRequestForRefund()
    {
        if (isset($_POST['addRequestForRefund'])) {

            $IdBuyAdvice = $_POST['IdBuyAdvice'];
            $ContentRequest = $_POST['ContentRequest'];
            $IdBuyer = $_POST['IdBuyer'];
            $IdSeller = $_POST['IdSeller'];

            $PictureRequestForRefund = [];
            if (isset($_FILES["PictureRequestForRefund"])) {
                if (count($_FILES["PictureRequestForRefund"]["tmp_name"]) > 10) {
                    echo "Vous pouvez télécharger un maximum de 10 images.";
                    return;
                }
                foreach ($_FILES["PictureRequestForRefund"]["tmp_name"] as $tmpName) {
                    if ($tmpName) {
                        $PictureRequestForRefund[] = file_get_contents($tmpName);
                    }
                }
            }

            $this->adviceMeetingModel->insertRequestForRefund($IdBuyAdvice, $ContentRequest, $IdBuyer, $IdSeller, $PictureRequestForRefund);
        }
    }
}
