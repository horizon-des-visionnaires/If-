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
        // Initialisation du chargeur de templates Twig
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        // Initialisation de l'environnement Twig
        $this->twig = new Environment($this->loader);
        // Instanciation du modèle adviceModel
        $this->adviceModel = new \advice\adviceModel();
    }

    public function advice()
    {
        session_start();

        $isConnected = false;
        $userId = null;
        $isPro = false;
        // Vérification de la connexion de l'utilisateur
        if (isset($_SESSION['IdUser'])) {
            $isConnected = true;
            $userId = $_SESSION['IdUser'];
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

        $this->getAdviceData();
        $this->getDataBuyAdvice();

        // Affichage du template Twig avec les données récupérées
        echo $this->twig->render('advice/advice.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'isPro' => $isPro,
            'adviceData' => $adviceData,
            'searchQuery' => $searchQuery,
            'sortBy' => $sortBy,
            'order' => $order
        ]);
    }

    public function getAdviceData()
    {
        if (isset($_POST['addAdvice'])) {
            $AdviceType = $_POST['AdviceType'];
            $AdviceDescription = $_POST['AdviceDescription'];
            $DaysOfWeekArray = $_POST['DaysOfWeek'] ?? []; // Liste des jours sélectionnés
            $StartTime = $_POST['StartTime'] ?? '';
            $EndTime = $_POST['EndTime'] ?? '';
            $IdUser = $_SESSION['IdUser'];

            // Vérifier que les deux champs de temps sont remplis
            if (empty($StartTime) || empty($EndTime)) {
                echo "Erreur : Heure de début et heure de fin doivent être remplies.";
                return;
            }

            // Vérifier qu'au moins un jour est sélectionné
            if (empty($DaysOfWeekArray)) {
                echo "Erreur : Au moins un jour doit être sélectionné.";
                return;
            }

            // Convertir le tableau des jours en une chaîne de caractères séparée par des virgules
            $DaysOfWeek = implode(',', $DaysOfWeekArray);

            // Insérer le conseil dans la base de données avec tous les jours dans une seule ligne
            $this->adviceModel->insertAdviceData(
                $AdviceType,
                $AdviceDescription,
                $IdUser,
                $DaysOfWeek,
                $StartTime,
                $EndTime
            );
        }
    }

    public function getDataBuyAdvice()
    {
        if (isset($_POST['buyAdvice'])) {
            $DaysOfWeek = $_POST['DaysOfWeek'];
            $StartTime = $_POST['StartTime'];
            $EndTime = $_POST['EndTime'];
            $IdAdvice = $_POST['IdAdvice'];
            $IdBuyer = $_SESSION['IdUser'];

            $this->adviceModel->buyAdvice($DaysOfWeek, $StartTime, $EndTime, $IdAdvice, $IdBuyer);
        }
    }
}
