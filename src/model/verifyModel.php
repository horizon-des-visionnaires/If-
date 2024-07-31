<?php

namespace verify;

use PDO;
use PDOException;

require_once 'database/connectDB.php';

class verifyModel
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

    public function verifyToken($token)
    {
        try {
            $query = "SELECT * FROM TempUser WHERE token = :token";
            $stmt = $this->dsn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $insertUser = "INSERT INTO User (FirstName, LastName, Email, UserPassword) VALUES (:FirstName, :LastName, :Email, :UserPassword)";
                $stmt2 = $this->dsn->prepare($insertUser);
                $stmt2->bindParam(':FirstName', $user['FirstName']);
                $stmt2->bindParam(':LastName', $user['LastName']);
                $stmt2->bindParam(':Email', $user['Email']);
                $stmt2->bindParam(':UserPassword', $user['UserPassword']);

                if ($stmt2->execute()) {
                    $deleteTempUser = "DELETE FROM TempUser WHERE token = :token";
                    $stmt3 = $this->dsn->prepare($deleteTempUser);
                    $stmt3->bindParam(':token', $token);
                    $stmt3->execute();
                    return true;
                }
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
}
