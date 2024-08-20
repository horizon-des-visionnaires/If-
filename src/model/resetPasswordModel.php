<?php

namespace resetPassword;

use PDO;
use PDOException;

require_once 'database/connectDB.php';

class resetPasswordModel
{
    private $dsn;

    public function __construct()
    {
        $this->dsn = connectDB();
    }

    // fonction pour modifier le mot de passe apres avoir reçu le token dans un mail
    public function resetPassword($token, $hashed_password)
    {
        $query = "SELECT * FROM TempTokenResetPassword WHERE token = :token";
        $stmt = $this->dsn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $updatePassword = "UPDATE User SET UserPassword = :UserPassword WHERE Email = :Email";
            $stmt2 = $this->dsn->prepare($updatePassword);
            $stmt2->bindParam(':Email', $user['Email']);
            $stmt2->bindParam(':UserPassword', $hashed_password);

            if ($stmt2->execute()) {
                $deleteTempReset = "DELETE FROM TempTokenResetPassword WHERE token = :token";
                $stmt3 = $this->dsn->prepare($deleteTempReset);
                $stmt3->bindParam(':token', $token);
                $stmt3->execute();

                header("Location: /login");
                exit;
            } else {
                return "Erreur lors de la modification du mot de passe.";
            }
        } else {
            return "Token incorrect.";
        }
    }
}
