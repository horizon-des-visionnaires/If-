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
        
        // Récupération des paramètres de filtre et de tri
        $searchQuery = $_GET['search'] ?? '';
        $sortBy = $_GET['sortBy'] ?? '';
        $order = $_GET['order'] ?? 'DESC';

        // Appel à la méthode du modèle pour obtenir les conseils filtrés
        $adviceData = $this->adviceModel->getFilteredAdvice($searchQuery, $sortBy, $order);

        // Affichage du template Twig avec les données récupérées
        echo $this->twig->render('advice/advice.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'adviceData' => $adviceData,
            'searchQuery' => $searchQuery,
            'sortBy' => $sortBy,
            'order' => $order
        ]);
    }

    public function getAdviceData()
    {
        // Traitement du formulaire d'ajout de conseil
        if (isset($_POST['addAdvice'])) {
            $AdviceType = $_POST['AdviceType'];
            $AdviceDescription = $_POST['AdviceDescription'];
            $IdUser = $_SESSION['IdUser'];

            $this->adviceModel->insertAdviceData($AdviceType, $AdviceDescription, $IdUser);
        }
    }
}
