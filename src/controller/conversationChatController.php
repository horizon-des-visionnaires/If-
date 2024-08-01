<?php

namespace conversationChat;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/conversationChatModel.php';

class conversationChatController
{
    protected $twig;
    private $loader;
    private $conversationChatModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->conversationChatModel = new conversationChatModel();
    }

    public function conversationChat($IdConversations)
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

        if (!$this->isParticipant($IdConversations, $userId)) {
            header("Location: /conversation");
            exit();
        }

        $participants = $this->conversationChatModel->getParticipants($IdConversations);

        $convChat = $this->conversationChatModel->getChat($IdConversations);
        $this->getMessage();

        echo $this->twig->render('conversationChat/conversationChat.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'convChat' => $convChat,
            'participants' => $participants
        ]);
    }

    public function getMessage()
    {
        if (isset($_POST['message'])) {
            $messageContent = $_POST['messageContent'];
            $IdConversations = $_POST['IdConversations'];
            $IdUser = $_SESSION['IdUser'];

            $this->conversationChatModel->insertMessage($IdConversations, $IdUser, $messageContent);
        }
    }

    private function isParticipant($IdConversations, $userId)
    {
        return $this->conversationChatModel->checkParticipant($IdConversations, $userId);
    }
}
