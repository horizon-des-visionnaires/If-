<?php

// Ensure this file is only included once
require_once 'database/connectDB.php';

function deleteUser(PDO $dsn, $idUser)
{
    try {
        $dsn->beginTransaction();

        // Delete associated records from tables with foreign key constraint
        $tables = [
            'LikeFavorites' => 'IdUser',
            'Comment' => 'IdUser',
            'RequestPassPro' => 'IdUser',
            'UserMessages' => 'IdUser',
            'Notations' => 'IdUser',
            'Conversations' => 'IdUser_1',
            'Conversations' => 'IdUser_2',
            'ConversationMessages' => 'IdSender',
            'Advice' => 'IdUser',
            'BuyAdvice' => 'IdBuyer',
            'Notifications' => 'IdUser',
        ];

        foreach ($tables as $table => $column) {
            $stmt = $dsn->prepare("DELETE FROM $table WHERE $column = :IdUser");
            $stmt->bindParam(':IdUser', $idUser);
            $stmt->execute();
        }

        // Now delete the user
        $stmt = $dsn->prepare("DELETE FROM User WHERE IdUser = :IdUser");
        $stmt->bindParam(':IdUser', $idUser);
        $stmt->execute();

        $dsn->commit();
    } catch (PDOException $e) {
        $dsn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

function deleteTempUser(PDO $dsn, $idTempUser)
{
    try {
        $stmt = $dsn->prepare("DELETE FROM TempUser WHERE IdTempUser = :IdTempUser");
        $stmt->bindParam(':IdTempUser', $idTempUser);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

function deleteTempTokenResetPassword(PDO $dsn, $email)
{
    try {
        $stmt = $dsn->prepare("DELETE FROM TempTokenResetPassword WHERE Email = :Email");
        $stmt->bindParam(':Email', $email);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

function deletePost(PDO $dsn, $idPost)
{
    try {
        $dsn->beginTransaction();

        // Delete associated records
        $stmt = $dsn->prepare("DELETE FROM PicturePost WHERE IdPost = :IdPost");
        $stmt->bindParam(':IdPost', $idPost);
        $stmt->execute();

        $stmt = $dsn->prepare("DELETE FROM Comment WHERE IdPost = :IdPost");
        $stmt->bindParam(':IdPost', $idPost);
        $stmt->execute();

        $stmt = $dsn->prepare("DELETE FROM LikeFavorites WHERE IdPost = :IdPost");
        $stmt->bindParam(':IdPost', $idPost);
        $stmt->execute();

        // Now delete the post
        $stmt = $dsn->prepare("DELETE FROM Post WHERE IdPost = :IdPost");
        $stmt->bindParam(':IdPost', $idPost);
        $stmt->execute();

        $dsn->commit();
    } catch (PDOException $e) {
        $dsn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

function deleteAdvice(PDO $dsn, $idAdvice)
{
    try {
        $dsn->beginTransaction();

        // Delete associated records
        $stmt = $dsn->prepare("DELETE FROM PictureAdvice WHERE IdAdvice = :IdAdvice");
        $stmt->bindParam(':IdAdvice', $idAdvice);
        $stmt->execute();

        $stmt = $dsn->prepare("DELETE FROM BuyAdvice WHERE IdAdvice = :IdAdvice");
        $stmt->bindParam(':IdAdvice', $idAdvice);
        $stmt->execute();

        // Now delete the advice
        $stmt = $dsn->prepare("DELETE FROM Advice WHERE IdAdvice = :IdAdvice");
        $stmt->bindParam(':IdAdvice', $idAdvice);
        $stmt->execute();

        $dsn->commit();
    } catch (PDOException $e) {
        $dsn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
