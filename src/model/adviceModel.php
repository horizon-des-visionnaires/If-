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

    public function insertAdviceData($AdviceType, $AdviceDescription, $IdUser, $DaysOfWeek, $StartTime, $EndTime)
    {
        try {
            $countAdviceQuery = "SELECT COUNT(*) FROM Advice WHERE IdUser = :IdUser";
            $stmtCount = $this->dsn->prepare($countAdviceQuery);
            $stmtCount->bindParam(':IdUser', $IdUser);
            $stmtCount->execute();
            $currentAdviceCount = $stmtCount->fetchColumn();

            if ($currentAdviceCount >= 3) {
                echo "User already has three advices, cannot insert more.";
                return;
            }

            $insertAdviceQuery = "INSERT INTO Advice (AdviceType, AdviceDescription, IdUser, DaysOfWeek, StartTime, EndTime)
            VALUES (:AdviceType, :AdviceDescription, :IdUser, :DaysOfWeek, :StartTime, :EndTime)";
            $execInsertAdvice = $this->dsn->prepare($insertAdviceQuery);
            $execInsertAdvice->bindParam(':AdviceType', $AdviceType);
            $execInsertAdvice->bindParam(':AdviceDescription', $AdviceDescription);
            $execInsertAdvice->bindParam(':IdUser', $IdUser);
            $execInsertAdvice->bindParam(':DaysOfWeek', $DaysOfWeek);
            $execInsertAdvice->bindParam(':StartTime', $StartTime);
            $execInsertAdvice->bindParam(':EndTime', $EndTime);
            $execInsertAdvice->execute();

            header('location: /advice');
            exit();
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function getAdviceAndUserInfo()
    {
        try {
            $query = "SELECT a.AdviceType, a.AdviceDescription, a.DaysOfWeek, a.StartTime, a.EndTime, p.IdUser, p.FirstName, p.LastName, p.ProfilPicture, p.ProfilPromotion
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

    public function getFilteredAdvice($searchQuery = '', $sortBy = '', $order = 'DESC')
    {
        $query = $this->buildAdviceQuery($searchQuery, $sortBy, $order);
        $stmt = $this->dsn->prepare($query);

        if ($searchQuery) {
            $searchQuery = "%{$searchQuery}%";
            $stmt->bindParam(':searchQuery', $searchQuery);
        }

        $stmt->execute();
        $adviceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($adviceData as &$advice) {
            if (isset($advice['ProfilPicture']) && $advice['ProfilPicture'] !== null) {
                $advice['ProfilPicture'] = base64_encode($advice['ProfilPicture']);
            } else {
                $advice['ProfilPicture'] = '';
            }
        }

        return $adviceData;
    }

    private function buildAdviceQuery($searchQuery, $sortBy, $order)
    {
        $query = "SELECT a.AdviceType, a.AdviceDescription, a.CreatedAt, a.DaysOfWeek, a.StartTime, a.EndTime, p.IdUser, p.FirstName, p.LastName, p.ProfilPicture, p.ProfilPromotion 
                  FROM Advice a
                  JOIN User p ON a.IdUser = p.IdUser";

        if ($searchQuery) {
            $query .= " WHERE (a.AdviceType LIKE :searchQuery 
                    OR a.AdviceDescription LIKE :searchQuery
                    OR p.FirstName LIKE :searchQuery 
                    OR p.LastName LIKE :searchQuery)";
        }

        if ($sortBy) {
            switch ($sortBy) {
                case 'type':
                    $query .= " ORDER BY a.AdviceType $order";
                    break;
                case 'user':
                    $query .= " ORDER BY p.FirstName $order, p.LastName $order";
                    break;
                default:
                    $query .= " ORDER BY a.CreatedAt $order";
                    break;
            }
        } else {
            $query .= " ORDER BY a.CreatedAt $order";
        }

        return $query;
    }
}
