<?php

namespace home;

use PDO;
use PDOException;

require_once 'database/connectDB.php';
require_once __DIR__ . '/utils.php';

class homeModel
{
    private $dsn;

    public function __construct()
    {
        $this->dsn = connectDB();
    }

    public function get5UserProRandom()
    {
        try {
            $stmt = $this->dsn->query("
            SELECT 
                u.IdUser,
                u.FirstName, 
                u.LastName, 
                u.ProfilPicture, 
                u.ProfilPromotion,
                a.AdviceType,
                a.AdviceDescription,
                pa.PictureAdvice
            FROM User u
            INNER JOIN Advice a ON u.IdUser = a.IdUser
            LEFT JOIN PictureAdvice pa ON a.IdAdvice = pa.IdAdvice
            WHERE u.IsPro = 1
            ORDER BY RAND()
            LIMIT 5
        ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $userData = [];
            foreach ($results as $row) {
                $userId = $row['IdUser'];

                if (!isset($userData[$userId])) {
                    $userData[$userId] = [
                        'IdUser' => $row['IdUser'],
                        'FirstName' => $row['FirstName'],
                        'LastName' => $row['LastName'],
                        'ProfilPicture' => $row['ProfilPicture'] ? base64_encode($row['ProfilPicture']) : null,
                        'ProfilPromotion' => $row['ProfilPromotion'],
                        'AdviceType' => $row['AdviceType'],
                        'AdviceDescription' => $row['AdviceDescription'],
                        'Pictures' => []
                    ];
                }

                if ($row['PictureAdvice']) {
                    $userData[$userId]['Pictures'][] = base64_encode($row['PictureAdvice']);
                }
            }

            return array_values($userData);
        } catch (PDOException $e) {
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }

    public function get5RandomPostsFromTop10()
    {
        try {
            $stmt = $this->dsn->query("
            SELECT 
                Post.IdPost, 
                Post.TitlePost, 
                Post.ContentPost, 
                Post.DatePost, 
                Post.Views, 
                Post.IdUser,
                User.FirstName,
                User.LastName,
                User.ProfilPicture,
                User.IsPro
            FROM Post
            INNER JOIN User ON Post.IdUser = User.IdUser
            ORDER BY Post.Views DESC
            LIMIT 10
        ");
            $top10Posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($top10Posts) > 5) {
                $randomKeys = array_rand($top10Posts, 5);
                $randomPosts = array_intersect_key($top10Posts, array_flip($randomKeys));
            } else {
                $randomPosts = $top10Posts;
            }

            foreach ($randomPosts as &$post) {
                if (!is_null($post['ProfilPicture'])) {
                    $post['ProfilPicture'] = base64_encode($post['ProfilPicture']);
                }
                $post['RelativeDatePost'] = $this->getRelativeTime($post['DatePost']);
            }

            return $randomPosts;
        } catch (PDOException $e) {
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }

    public function getRelativeTime($date)
    {
        return getRelativeTime($date);
    }

    public function getUserAdmin()
    {
        try {
            $stmt = $this->dsn->query("
                SELECT 
                    IdUser,
                    FirstName, 
                    LastName, 
                    ProfilPicture,
                    IsAdmin
                FROM User
                WHERE IsAdmin = 1
            ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as &$row) {
                if (!is_null($row['ProfilPicture'])) {
                    $row['ProfilPicture'] = base64_encode($row['ProfilPicture']);
                }
            }

            return $results;
        } catch (PDOException $e) {
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }

    public function addConversation($idUser_1, $IdUser_2)
    {
        try {
            $this->dsn->beginTransaction();

            $checkConv = "SELECT IdConversations FROM Conversations 
                      WHERE (IdUser_1 = :IdUser_1 AND IdUser_2 = :IdUser_2) 
                         OR (IdUser_1 = :IdUser_2 AND IdUser_2 = :IdUser_1)";
            $stmt = $this->dsn->prepare($checkConv);
            $stmt->bindParam(':IdUser_1', $idUser_1);
            $stmt->bindParam(':IdUser_2', $IdUser_2);
            $stmt->execute();

            $existingConversationId = $stmt->fetchColumn();

            if ($existingConversationId) {
                header("Location: /conversationChat-" . $existingConversationId);
                exit();
            } else {
                $addConv = "INSERT INTO Conversations (IdUser_1, IdUser_2) VALUES (:IdUser_1, :IdUser_2)";
                $stmt2 = $this->dsn->prepare($addConv);
                $stmt2->bindParam(':IdUser_1', $idUser_1);
                $stmt2->bindParam(':IdUser_2', $IdUser_2);
                $stmt2->execute();

                $idConversation = $this->dsn->lastInsertId();

                $addMessage = "INSERT INTO ConversationMessages (IdConversations, IdSender, ContentMessages) VALUES (:IdConversations, :IdSender, :ContentMessages)";
                $stmt3 = $this->dsn->prepare($addMessage);
                $contentMessage = "premier message";
                $stmt3->bindParam(':IdConversations', $idConversation);
                $stmt3->bindParam(':IdSender', $IdUser_2);
                $stmt3->bindParam(':ContentMessages', $contentMessage);
                $stmt3->execute();

                // Créer une notification pour l'utilisateur IdUser_1
                $MessageNotif = "Vous avez une nouvelle conversation avec " . $_SESSION['FirstName'] . " " . $_SESSION['LastName'];
                $addNotification = "INSERT INTO Notifications (IdUser, MessageNotif) VALUES (:IdUser, :MessageNotif)";
                $stmt4 = $this->dsn->prepare($addNotification);
                $stmt4->bindParam(':IdUser', $idUser_1);
                $stmt4->bindParam(':MessageNotif', $MessageNotif);
                $stmt4->execute();

                $this->dsn->commit();

                header("Location: /conversationChat-" . $idConversation);
                exit();
            }
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }
}
