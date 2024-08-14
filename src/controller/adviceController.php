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
    private $notificationModel;

    public function __construct()
    {
        // Initialisation du chargeur de templates Twig
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        // Initialisation de l'environnement Twig
        $this->twig = new Environment($this->loader);
        // Instanciation du modèle adviceModel
        $this->adviceModel = new \advice\adviceModel();
        $this->notificationModel = new \notification\notificationModel();
    }

    public function advice()
    {
        session_start();

        $isConnected = false;
        $userId = null;
        $isPro = false;
        $isAdmin = null;
        // Vérification de la connexion de l'utilisateur
        if (isset($_SESSION['IdUser'])) {
            $isConnected = true;
            $userId = $_SESSION['IdUser'];
            $isAdmin = $_SESSION['IsAdmin'];
        }

        $IsAdmin = false;
        // Vérification des droits administrateur
        if (isset($_SESSION['IsAdmin']) && $_SESSION['IsAdmin'] == 1) {
            $IsAdmin = true;
        }

        // Vérification du statut IsPro de l'utilisateur
        if (isset($_SESSION['IsPro']) && $_SESSION['IsPro'] == 1) {
            $isPro = true;
        }

        // Récupération des paramètres de filtre et de tri
        $searchQuery = $_GET['search'] ?? '';
        $sortBy = $_GET['sortBy'] ?? '';
        $order = $_GET['order'] ?? 'DESC';

        // Appel à la méthode du modèle pour obtenir les conseils filtrés
        $adviceData = $this->adviceModel->getFilteredAdvice($searchQuery, $sortBy, $order);

        $errorMessages = [];

        $this->getAdviceData($errorMessages);
        $this->getDataBuyAdvice($errorMessages);

        $unreadCount = $this->notificationModel->getUnreadNotificationCount($userId);
        $this->getDeleteAdviceData($errorMessages);

        $getCategory = $this->adviceModel->getCategory();

        // Affichage du template Twig avec les données récupérées
        echo $this->twig->render('advice/advice.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'isPro' => $isPro,
            'adviceData' => $adviceData,
            'searchQuery' => $searchQuery,
            'sortBy' => $sortBy,
            'order' => $order,
            'unreadCount' => $unreadCount,
            'errorMessages' => $errorMessages,
            'isAdmin' => $isAdmin,
            'getCategory' => $getCategory
        ]);
    }

    public function getAdviceData(&$errorMessages)
    {
        if (isset($_POST['addAdvice'])) {
            $AdviceType = $_POST['AdviceType'];
            $AdviceDescription = $_POST['AdviceDescription'];
            $DaysOfWeekArray = $_POST['DaysOfWeek'] ?? []; // Liste des jours sélectionnés
            $StartTime = $_POST['StartTime'] ?? '';
            $EndTime = $_POST['EndTime'] ?? '';
            $CategoryId = $_POST['CategoryId'];
            $IdUser = $_SESSION['IdUser'];

            // Vérifier que les deux champs de temps sont remplis
            if (empty($StartTime) || empty($EndTime)) {
                $errorMessages[] = "Erreur : Heure de début et heure de fin doivent être remplies.";
                return;
            }

            $startDateTime = new \DateTime($StartTime);
            $endDateTime = new \DateTime($EndTime);

            // Vérifier que EndTime n'est pas inférieur à StartTime
            if ($endDateTime <= $startDateTime) {
                $errorMessages[] = "Erreur : L'heure de fin doit être supérieure à l'heure de début.";
                return;
            }

            // Vérifier qu'au moins un jour est sélectionné
            if (empty($DaysOfWeekArray)) {
                $errorMessages[] = "Erreur : Au moins un jour doit être sélectionné.";
                return;
            }

            // Convertir le tableau des jours en une chaîne de caractères séparée par des virgules
            $DaysOfWeek = implode(',', $DaysOfWeekArray);

            $PictureAdvice = [];
            if (isset($_FILES["PictureAdvice"])) {
                if (count($_FILES["PictureAdvice"]["tmp_name"]) > 3) {
                    $errorMessages[] = "Vous pouvez télécharger un maximum de 3 images.";
                    return;
                }
                foreach ($_FILES["PictureAdvice"]["tmp_name"] as $tmpName) {
                    if ($tmpName) {
                        $PictureAdvice[] = file_get_contents($tmpName);
                    }
                }
            }

            // Insérer le conseil dans la base de données avec tous les jours dans une seule ligne
            $result = $this->adviceModel->insertAdviceData(
                $AdviceType,
                $AdviceDescription,
                $IdUser,
                $DaysOfWeek,
                $StartTime,
                $EndTime,
                $PictureAdvice,
                $CategoryId
            );

            if (is_string($result)) {
                $errorMessages[] = $result;
            }
        }
    }

    public function getDataBuyAdvice(&$errorMessages)
    {
        if (isset($_POST['buyAdvice'])) {
            $Date = $_POST['Date'];
            $StartTime = $_POST['StartTime'];
            $EndTime = $_POST['EndTime'];
            $IdAdvice = $_POST['IdAdvice'];
            $IdBuyer = $_SESSION['IdUser'];

            $result = $this->adviceModel->buyAdvice($Date, $StartTime, $EndTime, $IdAdvice, $IdBuyer);

            if (is_string($result)) {
                $errorMessages[] = $result;
            }
        }
    }

    public function getDeleteAdviceData($errorMessages)
    {
        if (isset($_POST['deleteAdvice'])) {
            $IdAdvice = $_POST['IdAdvice'];
            $result = $this->adviceModel->deleteAdvice($IdAdvice);

            if (is_string($result)) {
                $errorMessages[] = $result;
            }
        }
    }
}
