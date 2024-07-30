<?php

namespace forgotPassword;

use PDO;
use PDOException;

require_once 'database/connectDB.php';

class forgotPasswordModel
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

    public function storeTempForgotPasswordData($email, $token)
    {
        try {
            $checkEmail = "SELECT COUNT(*) FROM TempTokenResetPassword WHERE Email = :email";
            $stmt = $this->dsn->prepare($checkEmail);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                return "email déja utilisé";
            } else {
                $insertTemp = "INSERT INTO TempTokenResetPassword (Email, token) VALUES (:Email, :token)";
                $stmt2 = $this->dsn->prepare($insertTemp);
                $stmt2->bindParam(':Email', $email);
                $stmt2->bindParam(':token', $token);

                return $stmt2->execute();
            }
        } catch (PDOException $e) {
            return false;
        }
    }
}
