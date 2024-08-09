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
        $this->dsn = connectDB();
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
