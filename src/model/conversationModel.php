<?php

namespace conversation;

use PDO;
use PDOException;

require_once __DIR__ . '/../database/connectDB.php';

class conversationModel
{
    private $dsn;

    public function __construct()
    {
        $this->dsn = connectDB();
    }

    public function getUsersByConversation($userId)
    {
        $stmt = $this->dsn->prepare("
            SELECT DISTINCT u.FirstName, u.LastName, u.IsPro, u.ProfilPicture, u.IsAdmin, c.IdConversations
            FROM Conversations c
            JOIN User u ON u.IdUser = CASE
                WHEN c.IdUser_1 = :userId THEN c.IdUser_2
                ELSE c.IdUser_1
            END
            WHERE c.IdUser_1 = :userId OR c.IdUser_2 = :userId
        ");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($usersData as &$userData) {
            if ($userData['ProfilPicture'] !== null) {
                $userData['ProfilPicture'] = base64_encode($userData['ProfilPicture']);
            } else {
                $userData['ProfilPicture'] = '';
            }
        }

        return $usersData;
    }
}
