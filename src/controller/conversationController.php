<?php

namespace conversation;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/conversationModel.php';

class conversationController
{
    protected $twig;
    private $loader;
    private $conversationModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->conversationModel = new conversationModel();
    }

    public function conversation()
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

        $user = $this->conversationModel->getConvById($userId);
        $conversationUsers = $this->conversationModel->getUsersByConversation($userId);

        echo $this->twig->render('conversation/conversation.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'user' => $user,
            'conversationUsers' => $conversationUsers
        ]);
    }
}
