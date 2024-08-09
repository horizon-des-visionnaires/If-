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

    public function getRelativeTime($date)
    {
        return getRelativeTime($date);
    }

    public function deletePost($idPost, $idUser)
    {
        return deletePost($this->dsn, $idPost, $idUser);
    }

    public function getIsLike($IdUser, $IdPost) {
        return getIsLike($this->dsn, $IdUser, $IdPost);
    }

    public function getIsFavorites($IdUser, $IdPost) {
        return getIsFavorites($this->dsn, $IdUser, $IdPost);
    }

    public function LikeData($IdUser, $IdPost) {
        LikeData($this->dsn, $IdUser, $IdPost, "/postDetails-$IdPost");
    }

    public function FavoriteData($IdUser, $IdPost) {
        FavoriteData($this->dsn, $IdUser, $IdPost, "/postDetails-$IdPost");
    }

    public function getCommentCount($idPost) {
        return getCommentCount($this->dsn, $idPost);
    }
}
