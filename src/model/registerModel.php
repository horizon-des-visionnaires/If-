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
        $this->dsn = connectDB();
    }

    public function storeTempRegisterData($firstName, $lastName, $email, $hashed_password, $token)
    {
        try {

            $insertTemp = "INSERT INTO TempUser (FirstName, LastName, Email, UserPassword, token) VALUES (:FirstName, :LastName, :Email, :UserPassword, :token)";
            $stmt2 = $this->dsn->prepare($insertTemp);
            $stmt2->bindParam(':FirstName', $firstName);
            $stmt2->bindParam(':LastName', $lastName);
            $stmt2->bindParam(':Email', $email);
            $stmt2->bindParam(':UserPassword', $hashed_password);
            $stmt2->bindParam(':token', $token);

            return $stmt2->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function emailExists($email)
    {
        try {
            $checkEmail = "SELECT COUNT(*) FROM User WHERE Email = :email";
            $stmt = $this->dsn->prepare($checkEmail);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
