<?php

namespace dashboard;

use PDO;
use PDOException;

require_once __DIR__ . '/../database/connectDB.php';
require_once __DIR__ . '/utils.php';

class dashboardModel
{
    private $dsn;

    public function __construct()
    {
        $this->dsn = connectDB();
        $this->dsn = connectDB();
    }

    public function getAllRequestPassPro()
    {
        try {
            $stmt = $this->dsn->query("
                    SELECT 
                        rp.*, 
                        u.FirstName, 
                        u.LastName ,
                        u.Email
                    FROM 
                        RequestPassPro rp
                    LEFT JOIN 
                        User u 
                    ON 
                        rp.IdUser = u.IdUser
                    WHERE 
                        rp.IsRequestValid = 0
                ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as &$row) {
                if (!is_null($row['IdentityCardRecto'])) {
                    $row['IdentityCardRecto'] = base64_encode($row['IdentityCardRecto']);
                }
                if (!is_null($row['IdentityCardVerso'])) {
                    $row['IdentityCardVerso'] = base64_encode($row['IdentityCardVerso']);
                }
                if (!is_null($row['UserPicture'])) {
                    $row['UserPicture'] = base64_encode($row['UserPicture']);
                }
            }

            return $results;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }

    public function requestValid($IdRequest)
    {
        try {
            $this->dsn->beginTransaction();

            $updateRequestQuery = "UPDATE RequestPassPro SET IsRequestValid = 1 WHERE IdRequest = :IdRequest";
            $stmt = $this->dsn->prepare($updateRequestQuery);
            $stmt->execute([':IdRequest' => $IdRequest]);

            $getUserIdQuery = "SELECT IdUser FROM RequestPassPro WHERE IdRequest = :IdRequest AND IsRequestValid = 1";
            $stmt = $this->dsn->prepare($getUserIdQuery);
            $stmt->execute([':IdRequest' => $IdRequest]);
            $IdUser = $stmt->fetchColumn();

            if ($IdUser) {
                $updateUserQuery = "UPDATE User SET IsPro = 1 WHERE IdUser = :IdUser";
                $stmt = $this->dsn->prepare($updateUserQuery);
                $stmt->execute([':IdUser' => $IdUser]);
                $this->addUserMessage($IdUser, "Félicitations, votre demande pour être pro a été validée !");
            }

            $this->dsn->commit();

            header("Location: /dashboard");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function getRequestPassProById($IdRequest)
    {
        try {
            $stmt = $this->dsn->prepare("
                SELECT 
                    rp.*, 
                    u.FirstName, 
                    u.LastName,
                    u.Email
                FROM 
                    RequestPassPro rp
                LEFT JOIN 
                    User u 
                ON 
                    rp.IdUser = u.IdUser
                WHERE 
                    rp.IdRequest = :IdRequest
            ");
            $stmt->execute([':IdRequest' => $IdRequest]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                if (!is_null($row['IdentityCardRecto'])) {
                    $row['IdentityCardRecto'] = base64_encode($row['IdentityCardRecto']);
                }
                if (!is_null($row['IdentityCardVerso'])) {
                    $row['IdentityCardVerso'] = base64_encode($row['IdentityCardVerso']);
                }
                if (!is_null($row['UserPicture'])) {
                    $row['UserPicture'] = base64_encode($row['UserPicture']);
                }
            }

            return $row;
        } catch (PDOException $e) {
            $error = "error: " . $e->getMessage();
            echo $error;
        }

        return false;
    }
    public function addUserMessage($IdUser, $message)
    {
        try {
            $stmt = $this->dsn->prepare("INSERT INTO UserMessages (IdUser, Message) VALUES (:IdUser, :Message)");
            $stmt->execute([
                ':IdUser' => $IdUser,
                ':Message' => $message
            ]);
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function requestInvalid($IdRequest, $rejectReason)
    {
        try {
            $this->dsn->beginTransaction();

            // Mettre à jour la demande comme invalidée
            $updateRequestQuery = "UPDATE RequestPassPro SET IsRequestValid = -1 WHERE IdRequest = :IdRequest";
            $stmt = $this->dsn->prepare($updateRequestQuery);
            $stmt->execute([':IdRequest' => $IdRequest]);

            // Obtenir l'ID utilisateur associé à la demande
            $getUserIdQuery = "SELECT IdUser FROM RequestPassPro WHERE IdRequest = :IdRequest";
            $stmt = $this->dsn->prepare($getUserIdQuery);
            $stmt->execute([':IdRequest' => $IdRequest]);
            $IdUser = $stmt->fetchColumn();

            if ($IdUser) {
                // Ajouter le message de rejet pour l'utilisateur
                $this->addUserMessage($IdUser, "Désolé, votre demande pour être pro a été refusée. Raison : " . $rejectReason);
            }

            $this->dsn->commit();

            header("Location: /dashboard");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function getUserPro()
    {
        try {
            $getUser = "SELECT IdUser, FirstName, LastName, ProfilPicture FROM User WHERE IsPro = 1";
            $stmt = $this->dsn->prepare($getUser);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as &$row) {
                if (!is_null($row['ProfilPicture'])) {
                    $row['ProfilPicture'] = base64_encode($row['ProfilPicture']);
                }
            }

            return $results;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function deletePro($IdUser)
    {
        try {
            $this->dsn->beginTransaction();

            $updateStatusPro = "UPDATE User SET IsPro = 0 WHERE IdUser = :IdUser";
            $stmt = $this->dsn->prepare($updateStatusPro);
            $stmt->execute([':IdUser' => $IdUser]);

            $this->dsn->commit();
            header("Location: /dashboard");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function getUser()
    {
        try {
            $getUser = "SELECT IdUser, FirstName, LastName, Email, ProfilPicture, CreatedAt FROM User";
            $stmt = $this->dsn->prepare($getUser);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as &$row) {
                if (!is_null($row['ProfilPicture'])) {
                    $row['ProfilPicture'] = base64_encode($row['ProfilPicture']);
                }
                $row['RelativeDateUser'] = $this->getRelativeTime($row['CreatedAt']);

            }

            return $results;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function getRelativeTime($date)
    {
        return getRelativeTime($date);
    }

    public function countNumberUser()
    {
        try {

            $countNumberUser = $this->dsn->query("SELECT COUNT(*) FROM User");
            $countNumberUser->execute();
            $result = $countNumberUser->fetchColumn();

            return $result;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function countNumberAdviceSell()
    {
        try {

            $countNumberUser = $this->dsn->query("SELECT Number FROM NumberBuyAdvice");
            $countNumberUser->execute();
            $result = $countNumberUser->fetchColumn();

            return $result;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function countNumberPost()
    {
        try {

            $countNumberUser = $this->dsn->query("SELECT COUNT(*) FROM Post");
            $countNumberUser->execute();
            $result = $countNumberUser->fetchColumn();

            return $result;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function countNumberComment()
    {
        try {

            $countNumberUser = $this->dsn->query("SELECT COUNT(*) FROM Comment");
            $countNumberUser->execute();
            $result = $countNumberUser->fetchColumn();

            return $result;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
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
            header("Location: /dashboard");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function insertCategory($CategoryName)
    {
        try {
            $this->dsn->beginTransaction();

            $checkCategoryName = "SELECT COUNT(*) FROM Category WHERE CategoryName = :CategoryName";
            $checkCategoryNameStmt = $this->dsn->prepare($checkCategoryName);
            $checkCategoryNameStmt->bindParam(':CategoryName', $CategoryName);
            $checkCategoryNameStmt->execute();
            $checkCategoryNameCount = $checkCategoryNameStmt->fetchColumn();

            if ($checkCategoryNameCount > 0) {
                echo "Cette catégorie existe déjà.";
            } else {
                $addCategoryName = "INSERT INTO Category (CategoryName) VALUES (:CategoryName)";
                $addCategoryStmt = $this->dsn->prepare($addCategoryName);
                $addCategoryStmt->bindParam(':CategoryName', $CategoryName);
                $addCategoryStmt->execute();

                $this->dsn->commit();

                header("Location: /dashboard");
                exit();
            }
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function getCategory()
    {
        try {
            $queryCategory = "SELECT * FROM Category";
            $stmtCategory = $this->dsn->prepare($queryCategory);
            $stmtCategory->execute();
            $categoryData = $stmtCategory->fetchAll(PDO::FETCH_ASSOC);

            return $categoryData;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }

    public function deleteCategory($IdCategory)
    {
        try {
            $this->dsn->beginTransaction();

            $checkAdviceQuery = "SELECT COUNT(*) FROM Advice WHERE IdCategory = :IdCategory";
            $stmt = $this->dsn->prepare($checkAdviceQuery);
            $stmt->bindParam(':IdCategory', $IdCategory, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                echo "La catégorie est utilisée par des conseils (Advice). Suppression impossible.";
            }

            $deleteCategoryQuery = "DELETE FROM Category WHERE IdCategory = :IdCategory";
            $stmt = $this->dsn->prepare($deleteCategoryQuery);
            $stmt->bindParam(':IdCategory', $IdCategory, PDO::PARAM_INT);
            $stmt->execute();

            $this->dsn->commit();
            header("Location: /dashboard");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function getRequestForRefundData()
    {
        try {
            $stmt = $this->dsn->query("
            SELECT 
                uBuyer.IdUser AS BuyerId,
                uBuyer.FirstName AS BuyerFirstName,
                uBuyer.LastName AS BuyerLastName,
                uBuyer.ProfilPicture AS BuyerProfilePicture,
                uSeller.IdUser AS SellerId,
                uSeller.FirstName AS SellerFirstName,
                uSeller.LastName AS SellerLastName,
                uSeller.ProfilPicture AS SellerProfilePicture,
                a.AdviceType AS AdviceTitle,
                ba.Date AS AdviceDate,
                ba.StartTime AS AdviceStartTime,
                ba.EndTime AS AdviceEndTime,
                r.ContentRequest,
                r.IdRequestForRefund AS RequestId,
                rp.PictureRequest AS PictureRequest
            FROM RequestForRefund r
            LEFT JOIN BuyAdvice ba ON r.IdBuyAdvice = ba.IdBuyAdvice
            LEFT JOIN Advice a ON ba.IdAdvice = a.IdAdvice
            LEFT JOIN User uBuyer ON r.IdBuyer = uBuyer.IdUser
            LEFT JOIN User uSeller ON r.IdSeller = uSeller.IdUser
            LEFT JOIN RequestForRefundPicture rp ON r.IdRequestForRefund = rp.IdRequestForRefund
            ORDER BY r.IdRequestForRefund
        ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $refundData = [];
            foreach ($results as $row) {
                $requestId = $row['RequestId'];

                if (!isset($refundData[$requestId])) {
                    $refundData[$requestId] = [
                        'RequestId' => $row['RequestId'],
                        'Buyer' => [
                            'IdUser' => $row['BuyerId'],
                            'FirstName' => $row['BuyerFirstName'],
                            'LastName' => $row['BuyerLastName'],
                            'ProfilePicture' => $row['BuyerProfilePicture'] ? base64_encode($row['BuyerProfilePicture']) : null,
                        ],
                        'Seller' => [
                            'IdUser' => $row['SellerId'],
                            'FirstName' => $row['SellerFirstName'],
                            'LastName' => $row['SellerLastName'],
                            'ProfilePicture' => $row['SellerProfilePicture'] ? base64_encode($row['SellerProfilePicture']) : null,
                        ],
                        'Advice' => [
                            'Title' => $row['AdviceTitle'],
                            'Date' => $row['AdviceDate'],
                            'StartTime' => $row['AdviceStartTime'],
                            'EndTime' => $row['AdviceEndTime'],
                        ],
                        'ContentRequest' => $row['ContentRequest'],
                        'Pictures' => []
                    ];
                }

                if ($row['PictureRequest']) {
                    $refundData[$requestId]['Pictures'][] = base64_encode($row['PictureRequest']);
                }
            }

            return array_values($refundData);
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
            echo $error;
            return [];
        }
    }
}
