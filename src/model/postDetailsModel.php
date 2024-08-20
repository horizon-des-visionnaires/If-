<?php

namespace postDetails;

use PDO;
use PDOException;

require_once __DIR__ . '/../database/connectDB.php';
require_once __DIR__ . '/utils.php';

class postDetailsModel
{
    private $dsn;

    public function __construct()
    {
        $this->dsn = connectDB();
    }

    // fonction pour afficher les données d'un post
    public function getPost($idPost)
    {
        $stmt = $this->dsn->prepare(
            "SELECT Post.IdPost, Post.IdUser, Post.TitlePost, Post.ContentPost, Post.DatePost, Post.Views, User.FirstName, User.LastName, User.ProfilPicture, User.IsPro, User.ProfilPromotion 
        FROM Post 
        JOIN User ON Post.IdUser = User.IdUser
        WHERE Post.IdPost = :idPost"
        );
        $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
        $stmt->execute();
        $getPostData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getPostData as &$post) {
            if ($post['ProfilPicture'] !== null) {
                $post['ProfilPicture'] = base64_encode($post['ProfilPicture']);
            } else {
                $post['ProfilPicture'] = '';
            }
            $post['RelativeDatePost'] = $this->getRelativeTime($post['DatePost']);

            $stmtPictures = $this->dsn->prepare("SELECT PicturePost FROM PicturePost WHERE IdPost = :IdPost");
            $stmtPictures->bindParam(':IdPost', $post['IdPost']);
            $stmtPictures->execute();
            $pictures = $stmtPictures->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ($pictures as &$picture) {
                $picture = base64_encode($picture);
            }
            $post['PicturesPost'] = $pictures;

            $post['IsLike'] = $this->getIsLike($post['IdUser'], $post['IdPost']);
            $post['IsFavorites'] = $this->getIsFavorites($post['IdUser'], $post['IdPost']);

            $stmtLikes = $this->dsn->prepare("SELECT COUNT(*) AS TotalLikes FROM LikeFavorites WHERE IdPost = :IdPost AND IsLike = 1");
            $stmtLikes->bindParam(':IdPost', $post['IdPost']);
            $stmtLikes->execute();
            $totalLikes = $stmtLikes->fetch(PDO::FETCH_ASSOC)['TotalLikes'];
            $post['TotalLikes'] = $totalLikes;
        }

        return $getPostData;
    }

    // fonction pour afficher les commentaires lié a un post
    public function getComment($idPost)
    {
        $stmt = $this->dsn->prepare(
            "SELECT Comment.ContentComment, Comment.DateComment, Comment.IdUser, Comment.IdComment, User.FirstName, User.LastName, User.ProfilPicture, User.IsPro
        FROM Comment
        JOIN User ON Comment.IdUser = User.IdUser
        WHERE Comment.IdPost = :idPost"
        );
        $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
        $stmt->execute();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($comments as &$comment) {
            if ($comment['ProfilPicture'] !== null) {
                $comment['ProfilPicture'] = base64_encode($comment['ProfilPicture']);
            } else {
                $comment['ProfilPicture'] = '';
            }
            $comment['RelativeDateComment'] = $this->getRelativeTime($comment['DateComment']);
        }

        return $comments;
    }

    // fonction  pour ajouter un commentaires
    public function addComment($idPost, $ContentComment, $IdUser)
    {
        try {

            $this->dsn->beginTransaction();

            $stmt = $this->dsn->prepare("INSERT INTO Comment (ContentComment, IdUser, IdPost) VALUES (:ContentComment, :IdUser, :idPost)");
            $stmt->bindParam(':ContentComment', $ContentComment);
            $stmt->bindParam(':IdUser', $IdUser);
            $stmt->bindParam(':idPost', $idPost);
            $stmt->execute();
            $IdComment = $this->dsn->lastInsertId();

            $this->dsn->commit();
            return $IdComment;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }

    // fonction pour supprimer un commentaires
    public function deleteComment($idComment, $idPost)
    {
        try {
            $this->dsn->beginTransaction();

            $deleteComment = "DELETE FROM Comment WHERE IdComment = :IdComment";
            $stmt = $this->dsn->prepare($deleteComment);
            $stmt->bindParam(':IdComment', $idComment);
            $stmt->execute();

            $this->dsn->commit();

            header("Location: /postDetails-$idPost");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }

    // fonction lié au post et au like/favoris appeler depuis le fichier utils.php
    public function getRelativeTime($date)
    {
        return getRelativeTime($date);
    }

    public function deletePost($idPost, $idUser)
    {
        return deletePost($this->dsn, $idPost, $idUser);
    }

    public function getIsLike($IdUser, $IdPost)
    {
        return getIsLike($this->dsn, $IdUser, $IdPost);
    }

    public function getIsFavorites($IdUser, $IdPost)
    {
        return getIsFavorites($this->dsn, $IdUser, $IdPost);
    }

    public function LikeData($IdUser, $IdPost)
    {
        LikeData($this->dsn, $IdUser, $IdPost, "/postDetails-$IdPost");
    }

    public function FavoriteData($IdUser, $IdPost)
    {
        FavoriteData($this->dsn, $IdUser, $IdPost, "/postDetails-$IdPost");
    }

    public function getCommentCount($idPost)
    {
        return getCommentCount($this->dsn, $idPost);
    }

    // fonction pour modifier les données d'un post
    public function updatePostData($TitlePost, $ContentPost, $IdUser, $IdPost, $PicturesPost = [])
    {
        try {
            $this->dsn->beginTransaction();

            $setClauses = [];
            if (!empty($TitlePost)) {
                $setClauses[] = "TitlePost = :TitlePost";
            }
            if (!empty($ContentPost)) {
                $setClauses[] = "ContentPost = :ContentPost";
            }

            if (empty($setClauses) && empty($PicturesPost)) {
                echo "No fields to update.";
                return;
            }

            if (!empty($setClauses)) {
                $query = "UPDATE Post SET " . implode(', ', $setClauses) . " WHERE IdUser = :IdUser AND IdPost = :IdPost";
                $stmt = $this->dsn->prepare($query);

                $stmt->bindParam(':IdUser', $IdUser, PDO::PARAM_INT);
                $stmt->bindParam(':IdPost', $IdPost, PDO::PARAM_INT);

                if (!empty($TitlePost)) {
                    $stmt->bindParam(':TitlePost', $TitlePost);
                }
                if (!empty($ContentPost)) {
                    $stmt->bindParam(':ContentPost', $ContentPost);
                }

                if (!$stmt->execute()) {
                    throw new PDOException("Failed to update post.");
                }
            }

            if (!empty($PicturesPost)) {
                // First, delete existing images for the post
                $deleteQuery = "DELETE FROM PicturePost WHERE IdPost = :IdPost";
                $deleteStmt = $this->dsn->prepare($deleteQuery);
                $deleteStmt->bindParam(':IdPost', $IdPost, PDO::PARAM_INT);
                $deleteStmt->execute();

                // Insert new images
                $insertQuery = "INSERT INTO PicturePost (IdPost, PicturePost) VALUES (:IdPost, :PicturePost)";
                $insertStmt = $this->dsn->prepare($insertQuery);

                foreach ($PicturesPost as $picture) {
                    $insertStmt->bindParam(':IdPost', $IdPost, PDO::PARAM_INT);
                    $insertStmt->bindParam(':PicturePost', $picture, PDO::PARAM_LOB);

                    if (!$insertStmt->execute()) {
                        throw new PDOException("Failed to insert images.");
                    }
                }
            }

            $this->dsn->commit();
            header("Location: /postDetails-$IdPost");
            exit();
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            $error = "error: " . $e->getMessage();
            echo $error;
        }
    }
}
