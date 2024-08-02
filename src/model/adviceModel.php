<?php

namespace advice;

use PDO;
use PDOException;

require_once 'database/connectDB.php';

class adviceModel
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

    public function insertAdviceData($AdviceType, $AdviceDescription, $IdUser)
    {
        try {
            $checkUser = "SELECT COUNT(*) FROM Advice WHERE IdUser =:IdUser";
            $execCheckUser = $this->dsn->prepare($checkUser);
            $execCheckUser->bindParam(':IdUser', $IdUser);
            $execCheckUser->execute();
            $userExists = $execCheckUser->fetchColumn();

            if ($userExists) {
                echo "User already has advice, skipping insertion.";
                return;
            }

            $insertAdviceQuery = "INSERT INTO Advice (AdviceType, AdviceDescription, IdUser)
                              VALUES (:AdviceType, :AdviceDescription, :IdUser)";
            $execInsertAdvice = $this->dsn->prepare($insertAdviceQuery);
            $execInsertAdvice->bindParam(':AdviceType', $AdviceType);
            $execInsertAdvice->bindParam(':AdviceDescription', $AdviceDescription);
            $execInsertAdvice->bindParam(':IdUser', $IdUser);
            $execInsertAdvice->execute();

            header('location: /advice');
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function getAdviceAndUserInfo()
    {
        try {
            $query = "SELECT a.AdviceType, a.AdviceDescription, p.IdUser ,p.FirstName, p.LastName, p.ProfilPicture, p.ProfilPromotion
                  FROM Advice a
                  JOIN User p ON a.IdUser = p.IdUser";

            $stmt = $this->dsn->prepare($query);
            $stmt->execute();
            $userData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($userData as &$user) {
                if (isset($user['ProfilPicture']) && $user['ProfilPicture'] !== null) {
                    $user['ProfilPicture'] = base64_encode($user['ProfilPicture']);
                } else {
                    $user['ProfilPicture'] = '';
                }
            }

            return $userData;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
}
