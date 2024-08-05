<?php

namespace home;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/homeModel.php';

class homeController
{
    protected $twig;
    private $loader;
    private $homeModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->homeModel = new \home\homeModel();
    }

    public function home()
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

        $this->logOut();
        $userProData = $this->homeModel->get5UserProRandom();
        $randomPosts = $this->homeModel->get5RandomPostsFromTop10();
        $userAdmin = $this->homeModel->getUserAdmin();
        $this->getConversationData();

        echo $this->twig->render('home/home.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'userProData' => $userProData,
            'randomPosts' => $randomPosts,
            'userAdmin' => $userAdmin
        ]);
    }

    public function logOut()
    {
        if (isset($_POST['logOut'])) {
            session_unset();
            header("Location: /login");
        }
    }

    public function getConversationData()
    {
        if (isset($_POST['conversation'])) {
            $idUser_1 = $_POST['idUser_1'];
            $IdUser_2 = $_SESSION['IdUser'];

            $this->homeModel->addConvertation($idUser_1, $IdUser_2);
        }
    }
}
