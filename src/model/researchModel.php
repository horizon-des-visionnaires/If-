<?php

namespace research;

use PDO;
use PDOException;

require_once 'database/connectDB.php';

class researchModel
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

    public function getFilteredProUsers($searchQuery = '', $sortBy = '', $order = 'DESC')
    {
        $query = $this->buildProUserQuery($searchQuery, $sortBy, $order);
        $stmt = $this->dsn->prepare($query);

        if ($searchQuery) {
            $searchQuery = "%{$searchQuery}%";
            $stmt->bindParam(':searchQuery', $searchQuery);
        }

        $stmt->execute();
        $proUserData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($proUserData as &$user) {
            $this->processUser($user);
        }

        return $proUserData;
    }

    private function buildProUserQuery($searchQuery, $sortBy, $order)
    {
        $query = "SELECT IdUser, FirstName, LastName, Email, ProfilPicture, ProfilDescription, ProfilPromotion 
              FROM User 
              WHERE IsPro = 1";

        if ($searchQuery) {
            $query .= " AND (FirstName LIKE :searchQuery 
                    OR LastName LIKE :searchQuery)";
        }

        // switch ($sortBy) {
        //     case 'name':
        //         $query .= " ORDER BY FirstName $order, LastName $order";
        //         break;
        //     case 'promotion':
        //         $query .= " ORDER BY ProfilPromotion $order";
        //         break;
        //     default:
        //         $query .= " ORDER BY IdUser $order";
        //         break;
        // }

        return $query;
    }

    private function processUserPicture($picture)
    {
        return $picture !== null ? base64_encode($picture) : '';
    }

    private function processUser(&$user)
    {
        $user['ProfilPicture'] = $this->processUserPicture($user['ProfilPicture']);
    }
}
