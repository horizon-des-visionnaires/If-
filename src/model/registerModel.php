<?php

namespace register;

use PDO;
use PDOException;

require_once 'database/connectDB.php';

class registerModel
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

    public function storeTempRegisterData($firstName, $lastName, $email, $hashed_password, $token)
    {
        try {
            $checkEmail = "SELECT COUNT(*) FROM User WHERE Email = :email";
            $stmt = $this->dsn->prepare($checkEmail);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                return "email déja utilisé";
            } else {
                $insertTemp = "INSERT INTO TempUser (FirstName, LastName, Email, UserPassword, token) VALUES (:FirstName, :LastName, :Email, :UserPassword, :token)";
                $stmt2 = $this->dsn->prepare($insertTemp);
                $stmt2->bindParam(':FirstName', $firstName);
                $stmt2->bindParam(':LastName', $lastName);
                $stmt2->bindParam(':Email', $email);
                $stmt2->bindParam(':UserPassword', $hashed_password);
                $stmt2->bindParam(':token', $token);

                return $stmt2->execute();
            }
        } catch (PDOException $e) {
            return false;
        }
    }
}
