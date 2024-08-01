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

        $details = $this->conversationChatModel->getConversationDetails($IdConversations, $userId);

        if (!$details['isParticipant']) {
            header("Location: /conversation");
            exit();
        }

        $convChat = $this->conversationChatModel->getChat($IdConversations);
        $this->getMessage();

        echo $this->twig->render('conversationChat/conversationChat.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'convChat' => $convChat,
            'participants' => [
                'FirstName1' => $details['FirstName1'],
                'LastName1' => $details['LastName1'],
                'FirstName2' => $details['FirstName2'],
                'LastName2' => $details['LastName2'],
            ]
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
}
