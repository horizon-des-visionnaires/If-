<?php

namespace notification;

use PDO;
use PDOException;

require_once 'database/connectDB.php';

class notificationModel
{
    private $dsn;

    public function __construct()
    {
        $this->dsn = connectDB();
    }

    public function getUserNotifications($userId)
    {
        try {
            $stmt = $this->dsn->prepare("
            SELECT IdNotification, MessageNotif, IsRead, CreatedAt 
            FROM Notifications 
            WHERE IdUser = :IdUser 
            ORDER BY CreatedAt DESC
        ");
            $stmt->bindParam(':IdUser', $userId);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function markNotificationAsRead($notificationId)
    {
        try {
            $stmt = $this->dsn->prepare("
            UPDATE Notifications 
            SET IsRead = 1 
            WHERE IdNotification = :IdNotification
        ");
            $stmt->bindParam(':IdNotification', $notificationId);
            $stmt->execute();

            header("Location: /notification");
            exit();
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function getUnreadNotificationCount($userId)
    {
        try {
            $stmt = $this->dsn->prepare("
            SELECT COUNT(*) as count
            FROM Notifications 
            WHERE IdUser = :IdUser 
            AND IsRead = 0
        ");
            $stmt->bindParam(':IdUser', $userId);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return 0;
        }
    }
}
