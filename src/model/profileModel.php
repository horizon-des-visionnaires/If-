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
        $this->dsn = connectDB();
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

    public function getRelativeTime($date)
    {
        return getRelativeTime($date);
    }

    public function deletePost($idPost, $idUser)
    {
        return deletePost($this->dsn, $idPost, $idUser);
    }

    public function getIsLike($IdUser, $IdPost)
    {
        return getIsLike($this->dsn, $IdUser, $IdPost);
    }

    public function getIsFavorites($IdUser, $IdPost)
    {
        return getIsFavorites($this->dsn, $IdUser, $IdPost);
    }

    public function LikeData($IdUser, $IdPost)
    {
        LikeData($this->dsn, $IdUser, $IdPost, "/profile-$IdUser");
    }

    public function FavoriteData($IdUser, $IdPost)
    {
        FavoriteData($this->dsn, $IdUser, $IdPost, "/profile-$IdUser");
    }

    public function getCommentCount($idPost)
    {
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

    public function getBuyAdviceData($userId)
    {
        try {
            $query = "
        SELECT 
            A.IdAdvice,
            A.AdviceType,
            A.AdviceDescription,
            C.CategoryName,
            BA.IdBuyAdvice,
            BA.Date AS BuyAdviceDate,
            BA.StartTime AS BuyAdviceStartTime,
            BA.EndTime AS BuyAdviceEndTime,
            U1.IdUser AS SellerId,
            U1.FirstName AS SellerFirstName,
            U1.LastName AS SellerLastName,
            U1.ProfilPicture AS SellerProfilPicture,
            U2.IdUser AS BuyerId,
            U2.FirstName AS BuyerFirstName,
            U2.LastName AS BuyerLastName,
            U2.ProfilPicture AS BuyerProfilPicture
        FROM BuyAdvice BA
        INNER JOIN Advice A ON BA.IdAdvice = A.IdAdvice
        INNER JOIN User U1 ON A.IdUser = U1.IdUser -- Seller
        INNER JOIN Category C ON A.IdCategory = C.IdCategory
        INNER JOIN User U2 ON BA.IdBuyer = U2.IdUser -- Buyer
        WHERE BA.IdBuyer = :userId OR A.IdUser = :userId
        ";

            $stmt = $this->dsn->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $adviceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Encode profile pictures
            foreach ($adviceData as &$advice) {
                $advice['SellerProfilPicture'] = $advice['SellerProfilPicture'] ? base64_encode($advice['SellerProfilPicture']) : '';
                $advice['BuyerProfilPicture'] = $advice['BuyerProfilPicture'] ? base64_encode($advice['BuyerProfilPicture']) : '';
            }

            return $adviceData;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    public function getAdviceImages($IdAdvice)
    {
        try {
            $query = "
            SELECT PictureAdvice
            FROM PictureAdvice
            WHERE IdAdvice = :idAdvice
        ";

            $stmt = $this->dsn->prepare($query);
            $stmt->bindParam(':idAdvice', $IdAdvice, PDO::PARAM_INT);
            $stmt->execute();

            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Encode images to base64
            foreach ($images as &$image) {
                $image['PictureAdvice'] = base64_encode($image['PictureAdvice']);
            }

            return $images;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return []; // Return an empty array on error
        }
    }

    public function deleteUser($IdUser)
    {
        try {
            $this->dsn->beginTransaction();

            // 1. Supprimer les likes et favoris associés aux posts de l'utilisateur
            $stmt = $this->dsn->prepare("DELETE FROM LikeFavorites WHERE IdPost IN (SELECT IdPost FROM Post WHERE IdUser = :IdUser)");
            $stmt->execute([':IdUser' => $IdUser]);

            // 2. Supprimer les commentaires liés aux posts de l'utilisateur
            $stmt = $this->dsn->prepare("DELETE FROM Comment WHERE IdPost IN (SELECT IdPost FROM Post WHERE IdUser = :IdUser)");
            $stmt->execute([':IdUser' => $IdUser]);

            // 3. Supprimer les photos des posts de l'utilisateur
            $stmt = $this->dsn->prepare("DELETE FROM PicturePost WHERE IdPost IN (SELECT IdPost FROM Post WHERE IdUser = :IdUser)");
            $stmt->execute([':IdUser' => $IdUser]);

            // 4. Supprimer les posts de l'utilisateur
            $stmt = $this->dsn->prepare("DELETE FROM Post WHERE IdUser = :IdUser");
            $stmt->execute([':IdUser' => $IdUser]);

            // 5. Supprimer les achats d'avis associés à l'utilisateur
            $stmt = $this->dsn->prepare("DELETE FROM BuyAdvice WHERE IdBuyer = :IdUser");
            $stmt->execute([':IdUser' => $IdUser]);

            // 6. Supprimer les notations faites par l'utilisateur
            $stmt = $this->dsn->prepare("DELETE FROM Notations WHERE IdUser = :IdUser");
            $stmt->execute([':IdUser' => $IdUser]);

            // 7. Supprimer les photos associées aux conseils de l'utilisateur
            $stmt = $this->dsn->prepare("DELETE FROM PictureAdvice WHERE IdAdvice IN (SELECT IdAdvice FROM Advice WHERE IdUser = :IdUser)");
            $stmt->execute([':IdUser' => $IdUser]);

            // 8. Supprimer les conseils associés à l'utilisateur
            $stmt = $this->dsn->prepare("DELETE FROM Advice WHERE IdUser = :IdUser");
            $stmt->execute([':IdUser' => $IdUser]);

            // 9. Supprimer les messages de conversation envoyés par l'utilisateur
            $stmt = $this->dsn->prepare("DELETE FROM ConversationMessages WHERE IdSender = :IdUser");
            $stmt->execute([':IdUser' => $IdUser]);

            // 10. Supprimer les conversations où l'utilisateur est participant
            $stmt = $this->dsn->prepare("DELETE FROM Conversations WHERE IdUser_1 = :IdUser OR IdUser_2 = :IdUser");
            $stmt->execute([':IdUser' => $IdUser]);

            // 11. Supprimer les messages de l'utilisateur
            $stmt = $this->dsn->prepare("DELETE FROM UserMessages WHERE IdUser = :IdUser");
            $stmt->execute([':IdUser' => $IdUser]);

            // 12. Supprimer les demandes de passage au statut pro de l'utilisateur
            $stmt = $this->dsn->prepare("DELETE FROM RequestPassPro WHERE IdUser = :IdUser");
            $stmt->execute([':IdUser' => $IdUser]);

            // 14. Supprimer les notifications de l'utilisateur
            $stmt = $this->dsn->prepare("DELETE FROM Notifications WHERE IdUser = :IdUser");
            $stmt->execute([':IdUser' => $IdUser]);

            // 15. Supprimer l'utilisateur lui-même
            $stmt = $this->dsn->prepare("DELETE FROM User WHERE IdUser = :IdUser");
            $stmt->execute([':IdUser' => $IdUser]);

            $this->dsn->commit();
            header("Location: /register");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function getNotationsById($id)
    {
        try {
            $getData = "
            SELECT 
                u.FirstName,
                u.LastName,
                u.ProfilPicture,
                u.IsPro,
                n.Note,
                n.CommentNote
            FROM 
                Notations n
            JOIN 
                User u ON n.IdUser = u.IdUser
            WHERE 
                n.IdUserIsPro = :id
        ";

            $stmt = $this->dsn->prepare($getData);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $notations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Débogage pour vérifier la requête
            if ($stmt->errorCode() != '00000') {
                $errorInfo = $stmt->errorInfo();
                echo 'SQL Error: ' . $errorInfo[2];
            }

            foreach ($notations as &$notation) {
                if ($notation['ProfilPicture']) {
                    $notation['ProfilPicture'] = base64_encode($notation['ProfilPicture']);
                }
            }

            return $notations;
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return [];
        }
    }
}
