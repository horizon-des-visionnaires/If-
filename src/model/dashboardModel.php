<?php

namespace dashboard;

use PDO;
use PDOException;

require_once __DIR__ . '/../database/connectDB.php';

class dashboardModel
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

            header("Location: /ifadev/src/index.php/dashboard");
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

                header("Location: /ifadev/src/index.php/dashboard");
                exit();
            } catch (PDOException $e) {
                $this->dsn->rollBack();
                echo "Erreur : " . $e->getMessage();
            }
        }
    }