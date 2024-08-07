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
            $convChat['CreatedAt'] = $this->getRelativeTime($convChat['CreatedAt']);
        }

        return $getConvChatData;
    }

    public function insertMessage($IdConversations, $IdUser, $messageContent)
    {
        try {
            $stmt = $this->dsn->prepare(
                "SELECT COUNT(*) FROM Conversations
                WHERE IdConversations = :IdConversations 
                AND (IdUser_1 = :IdSender OR IdUser_2 = :IdSender)"
            );
            $stmt->bindParam(':IdConversations', $IdConversations, PDO::PARAM_INT);
            $stmt->bindParam(':IdSender', $IdUser, PDO::PARAM_INT);
            $stmt->execute();
            $isParticipant = $stmt->fetchColumn();

            if ($isParticipant) {
                $stmt = $this->dsn->prepare(
                    "INSERT INTO ConversationMessages (IdConversations, IdSender, ContentMessages)
                    VALUES (:IdConversations, :IdSender, :ContentMessages)"
                );
                $stmt->bindParam(':IdConversations', $IdConversations, PDO::PARAM_INT);
                $stmt->bindParam(':IdSender', $IdUser, PDO::PARAM_INT);
                $stmt->bindParam(':ContentMessages', $messageContent, PDO::PARAM_STR);
                $stmt->execute();
                header("Location: /conversationChat-" . $IdConversations);
                exit();
            } else {
                echo "Erreur : L'utilisateur n'est pas autorisé à envoyer des messages dans cette conversation.";
            }
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }

    public function getConversationDetails($IdConversations, $userId)
    {
        $stmt = $this->dsn->prepare(
            "SELECT User1.FirstName AS FirstName1, User1.LastName AS LastName1, 
                    User2.FirstName AS FirstName2, User2.LastName AS LastName2,
                    (User1.IdUser = :userId OR User2.IdUser = :userId) AS isParticipant
             FROM Conversations
             JOIN User AS User1 ON Conversations.IdUser_1 = User1.IdUser
             JOIN User AS User2 ON Conversations.IdUser_2 = User2.IdUser
             WHERE Conversations.IdConversations = :IdConversations"
        );
        $stmt->bindParam(':IdConversations', $IdConversations, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRelativeTime($date)
    {
        return getRelativeTime($date);
    }
}
