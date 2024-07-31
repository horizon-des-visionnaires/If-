<?php

namespace conversationChat;

use PDO;
use PDOException;

require_once __DIR__ . '/../database/connectDB.php';

class conversationChatModel
{
    private $dsn;

    public function __construct()
    {
        $this->connectDB();
    }

    public function connectDB()
    {
        $this->dsn = new PDO("mysql:host=mysql;dbname=ifa_database", "ifa_user", "ifa_password");
        $this->dsn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getChat($IdConversations)
    {
        $stmt = $this->dsn->prepare(
            "SELECT ConversationMessages.IdMessages, ConversationMessages.IdConversations, ConversationMessages.IdSender, ConversationMessages.ContentMessages, ConversationMessages.CreatedAt,
            User.FirstName, User.LastName, User.ProfilPicture, User.IsPro 
        FROM ConversationMessages 
        JOIN User ON ConversationMessages.IdSender = User.IdUser
        WHERE ConversationMessages.IdConversations = :IdConversations"
        );
        $stmt->bindParam(':IdConversations', $IdConversations, PDO::PARAM_INT);
        $stmt->execute();
        $getConvChatData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getConvChatData as &$convChat) {
            if ($convChat['ProfilPicture'] !== null) {
                $convChat['ProfilPicture'] = base64_encode($convChat['ProfilPicture']);
            } else {
                $convChat['ProfilPicture'] = '';
            }
        }

        return $getConvChatData;
    }
}
