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
        $this->connectDB();
    }

    public function connectDB()
    {
        $this->dsn = new PDO("mysql:host=mysql;dbname=ifa_database", "ifa_user", "ifa_password");
        $this->dsn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
}
