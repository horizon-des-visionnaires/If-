<?php

namespace profile;

use PDO;
use PDOException;

require_once __DIR__ . '/../database/connectDB.php';
require_once __DIR__ . '/utils.php';

class profileModel
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

    public function getUserById($id)
    {
        $stmt = $this->dsn->prepare("SELECT * FROM User WHERE IdUser = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $getUserData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($getUserData === false) {
            return null;
        }

        foreach ($getUserData as $key => &$User) {
            if ($key === 'ProfilPicture') {
                if ($User !== null) {
                    $User = base64_encode($User);
                } else {
                    $User = '';
                }
            }
        }

        return $getUserData;
    }


    public function updateUserData($IdUser, $FirstName, $LastName, $ProfilDescription, $ProfilPromotion, $Location, $ProfilPicture = null)
    {
        try {
            $setClauses = [];

            if (!empty($FirstName)) {
                $setClauses[] = "FirstName = :FirstName";
            }
            if (!empty($LastName)) {
                $setClauses[] = "LastName = :LastName";
            }
            if (!empty($ProfilDescription)) {
                $setClauses[] = "ProfilDescription = :ProfilDescription";
            }
            if (!empty($ProfilPromotion)) {
                $setClauses[] = "ProfilPromotion = :ProfilPromotion";
            }
            if (!empty($Location)) {
                $setClauses[] = "Location = :Location";
            }
            if (!empty($ProfilPicture)) {
                $setClauses[] = "ProfilPicture = :ProfilPicture";
            }

            if (empty($setClauses)) {
                echo "Aucun champ à mettre à jour.";
                return;
            }

            $query = "UPDATE User SET " . implode(', ', $setClauses) . " WHERE IdUser = :IdUser";
            $stmt = $this->dsn->prepare($query);

            $stmt->bindParam(':IdUser', $IdUser, PDO::PARAM_INT);

            if (!empty($FirstName)) {
                $stmt->bindParam(':FirstName', $FirstName);
            }
            if (!empty($LastName)) {
                $stmt->bindParam(':LastName', $LastName);
            }
            if (!empty($ProfilDescription)) {
                $stmt->bindParam(':ProfilDescription', $ProfilDescription);
            }
            if (!empty($ProfilPromotion)) {
                $stmt->bindParam(':ProfilPromotion', $ProfilPromotion);
            }
            if (!empty($Location)) {
                $stmt->bindParam(':Location', $Location);
            }
            if (!empty($ProfilPicture)) {
                $stmt->bindParam(':ProfilPicture', $ProfilPicture, PDO::PARAM_LOB);
            }

            if ($stmt->execute()) {
                header("Location: /profile-$IdUser");
                exit();
            } else {
                echo "Échec de la mise à jour.";
            }
        } catch (PDOException $e) {
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }

    public function getUserPosts($id)
    {
        $stmt = $this->dsn->prepare(
            "SELECT Post.IdPost, Post.TitlePost, Post.ContentPost, Post.DatePost, Post.Views, Post.IdUser, User.FirstName, User.LastName, User.ProfilPicture 
            FROM Post 
            JOIN User ON Post.IdUser = User.IdUser
            WHERE User.IdUser = :id
            ORDER BY Post.DatePost DESC"
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $getPostData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getPostData as &$post) {
            if ($post['ProfilPicture'] !== null) {
                $post['ProfilPicture'] = base64_encode($post['ProfilPicture']);
            } else {
                $post['ProfilPicture'] = '';
            }
            $post['RelativeDatePost'] = $this->getRelativeTime($post['DatePost']);

            $stmtPictures = $this->dsn->prepare("SELECT PicturePost FROM PicturePost WHERE IdPost = :IdPost");
            $stmtPictures->bindParam(':IdPost', $post['IdPost']);
            $stmtPictures->execute();
            $pictures = $stmtPictures->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ($pictures as &$picture) {
                $picture = base64_encode($picture);
            }
            $post['PicturesPost'] = $pictures;

            $post['IsLike'] = $this->getIsLike($post['IdUser'], $post['IdPost']);
            $post['IsFavorites'] = $this->getIsFavorites($post['IdUser'], $post['IdPost']);

            $stmtLikes = $this->dsn->prepare("SELECT COUNT(*) AS TotalLikes FROM LikeFavorites WHERE IdPost = :IdPost AND IsLike = 1");
            $stmtLikes->bindParam(':IdPost', $post['IdPost']);
            $stmtLikes->execute();
            $totalLikes = $stmtLikes->fetch(PDO::FETCH_ASSOC)['TotalLikes'];
            $post['TotalLikes'] = $totalLikes;
        }

        return $getPostData;
    }

    private function getRelativeTime($date)
    {
        $timestamp = strtotime($date);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return $diff . ' s';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' m';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' h';
        } else {
            return floor($diff / 86400) . ' J';
        }
    }

    public function insertRequestPassProData($Job, $Age, $Description, $idUser, $Adress, $identityCardRecto = null, $identityCardVerso = null, $UserPicture = null)
    {
        try {
            $this->dsn->beginTransaction();

            $insertData = "INSERT INTO RequestPassPro (IdUser, UserJob, UserAge, Description, IdentityCardRecto, IdentityCardVerso, UserPicture, UserAdress) VALUE (:IdUser, :UserJob, :UserAge, :Description, :IdentityCardRecto, :IdentityCardVerso, :UserPicture, :UserAdress)";
            $stmt = $this->dsn->prepare($insertData);
            $stmt->bindParam(':IdUser', $idUser);
            $stmt->bindParam(':UserJob', $Job);
            $stmt->bindParam(':UserAge', $Age);
            $stmt->bindParam(':Description', $Description);
            $stmt->bindParam(':IdentityCardRecto', $identityCardRecto, PDO::PARAM_LOB);
            $stmt->bindParam(':IdentityCardVerso', $identityCardVerso, PDO::PARAM_LOB);
            $stmt->bindParam(':UserPicture', $UserPicture, PDO::PARAM_LOB);
            $stmt->bindParam(':UserAdress', $Adress);
            $stmt->execute();

            $this->dsn->commit();

            header("Location: /profile-$idUser");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }
    
    public function deletePost($idPost, $idUser)
    {
        return deletePost($this->dsn, $idPost, $idUser);
    }

    public function getIsLike($IdUser, $IdPost) {
        return getIsLike($this->dsn, $IdUser, $IdPost);
    }

    public function getIsFavorites($IdUser, $IdPost) {
        return getIsFavorites($this->dsn, $IdUser, $IdPost);
    }

    public function LikeData($IdUser, $IdPost) {
        LikeData($this->dsn, $IdUser, $IdPost, "/profile-$IdUser");
    }

    public function FavoriteData($IdUser, $IdPost) {
        FavoriteData($this->dsn, $IdUser, $IdPost, "/profile-$IdUser");
    }

    public function getCommentCount($idPost) {
        return getCommentCount($this->dsn, $idPost);
    }

    public function updateViews($idPost)
    {
        return updateViews($this->dsn, $idPost);
    }

    public function getUserFavorites($id)
    {
        $stmt = $this->dsn->prepare(
            "SELECT Post.IdPost, Post.TitlePost, Post.ContentPost, Post.DatePost, Post.Views, Post.IdUser, User.FirstName, User.LastName, User.ProfilPicture 
        FROM Post 
        JOIN User ON Post.IdUser = User.IdUser
        JOIN LikeFavorites ON Post.IdPost = LikeFavorites.IdPost
        WHERE LikeFavorites.IdUser = :id AND LikeFavorites.IsFavorites = 1
        ORDER BY Post.DatePost DESC"
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $getFavPostData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getFavPostData as &$postFav) {
            if ($postFav['ProfilPicture'] !== null) {
                $postFav['ProfilPicture'] = base64_encode($postFav['ProfilPicture']);
            } else {
                $postFav['ProfilPicture'] = '';
            }
            $postFav['RelativeDatePost'] = $this->getRelativeTime($postFav['DatePost']);

            $stmtPictures = $this->dsn->prepare("SELECT PicturePost FROM PicturePost WHERE IdPost = :IdPost");
            $stmtPictures->bindParam(':IdPost', $postFav['IdPost']);
            $stmtPictures->execute();
            $pictures = $stmtPictures->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ($pictures as &$picture) {
                $picture = base64_encode($picture);
            }
            $postFav['PicturesPost'] = $pictures;

            $postFav['IsLike'] = $this->getIsLike($postFav['IdUser'], $postFav['IdPost']);
            $postFav['IsFavorites'] = true;

            $stmtLikes = $this->dsn->prepare("SELECT COUNT(*) AS TotalLikes FROM LikeFavorites WHERE IdPost = :IdPost AND IsLike = 1");
            $stmtLikes->bindParam(':IdPost', $postFav['IdPost']);
            $stmtLikes->execute();
            $totalLikes = $stmtLikes->fetch(PDO::FETCH_ASSOC)['TotalLikes'];
            $postFav['TotalLikes'] = $totalLikes;
        }

        return $getFavPostData;
    }

    public function getUserMessages($id)
    {
        try {
            $stmt = $this->dsn->prepare("SELECT Message FROM UserMessages WHERE IdUser = :IdUser AND CreatedAt > NOW() - INTERVAL 24 HOUR");
            $stmt->execute([':IdUser' => $id]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return [];
        }
    }

    public function cleanupOldData()
    {
        try {
            $this->dsn->beginTransaction();

            // Supprimer les messages plus anciens que 24 heures
            $deleteOldMessages = "DELETE FROM UserMessages WHERE CreatedAt <= NOW() - INTERVAL 24 HOUR";
            $stmtMessages = $this->dsn->prepare($deleteOldMessages);
            $stmtMessages->execute();

            // Supprimer les demandes plus anciennes que 24 heures
            $deleteOldRequests = "DELETE FROM RequestPassPro WHERE CreatedAt <= NOW() - INTERVAL 24 HOUR";
            $stmtRequests = $this->dsn->prepare($deleteOldRequests);
            $stmtRequests->execute();

            $this->dsn->commit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function addConvertation($idUser_1, $IdUser_2)
    {
        try {
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

                header("Location: /conversationChat-" . $idConversation);
                exit();
            }
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }
}
