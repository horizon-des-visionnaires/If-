<?php

namespace allPost;

use PDO;
use PDOException;

require_once 'database/connectDB.php';
require_once __DIR__ . '/utils.php';

class allPostModel
{
    private $dsn;

    public function __construct()
    {
        $this->dsn = connectDB();
    }

    // fonction pour ajouter un post
    public function addPost($TitlePost, $ContentPost, $PicturesPost, $IdUser)
    {
        try {
            $this->dsn->beginTransaction();

            $stmt = $this->dsn->prepare("INSERT INTO Post (TitlePost, ContentPost, IdUser) VALUES (:TitlePost, :ContentPost, :IdUser)");
            $stmt->bindParam(':TitlePost', $TitlePost);
            $stmt->bindParam(':ContentPost', $ContentPost);
            $stmt->bindParam(':IdUser', $IdUser);
            $stmt->execute();
            $IdPost = $this->dsn->lastInsertId();

            $stmt = $this->dsn->prepare("INSERT INTO PicturePost (IdPost, PicturePost) VALUES (:IdPost, :PicturePost)");
            foreach ($PicturesPost as $PicturePost) {
                $stmt->bindParam(':IdPost', $IdPost);
                $stmt->bindParam(':PicturePost', $PicturePost, PDO::PARAM_LOB);
                $stmt->execute();
            }

            $this->dsn->commit();
            return $IdPost;
        } catch (PDOException $e) {
            $this->dsn->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }

    // fonction pour filtrer et afficher le spost les post
    public function getFilteredPosts($searchQuery = '', $sortBy = '', $order = 'DESC')
    {
        $query = $this->buildQuery($searchQuery, $sortBy, $order);
        $stmt = $this->dsn->prepare($query);

        if ($searchQuery) {
            $searchQuery = "%{$searchQuery}%";
            $stmt->bindParam(':searchQuery', $searchQuery);
        }

        $stmt->execute();
        $getPostData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getPostData as &$post) {
            $this->processPost($post);
        }

        return $getPostData;
    }

    // fonction pour filtrer les post
    private function buildQuery($searchQuery, $sortBy, $order)
    {
        $query = "SELECT Post.IdPost, Post.TitlePost, Post.ContentPost, Post.DatePost, Post.Views,
              User.IdUser, User.FirstName, User.LastName, User.ProfilPicture, User.IsPro, User.ProfilPromotion
              FROM Post 
              JOIN User ON Post.IdUser = User.IdUser";

        if ($searchQuery) {
            $query .= " WHERE Post.TitlePost LIKE :searchQuery 
                    OR User.FirstName LIKE :searchQuery 
                    OR User.LastName LIKE :searchQuery";
        }

        switch ($sortBy) {
            case 'likes':
                $query .= " ORDER BY (SELECT COUNT(*) FROM LikeFavorites WHERE IdPost = Post.IdPost AND IsLike = 1) $order";
                break;
            case 'views':
                $query .= " ORDER BY Post.Views $order";
                break;
            case 'comments':
                $query .= " ORDER BY (SELECT COUNT(*) FROM Comment WHERE IdPost = Post.IdPost) $order";
                break;
            case 'date':
            default:
                $query .= " ORDER BY Post.DatePost $order";
                break;
        }

        return $query;
    }

    // fonction pour récupérer toutes les donées lié a un post
    private function processPost(&$post)
    {
        $post['ProfilPicture'] = $this->processProfilePicture($post['ProfilPicture']);
        $post['RelativeDatePost'] = $this->getRelativeTime($post['DatePost']);
        $post['PicturesPost'] = $this->getPictures($post['IdPost']);
        $post['IsLike'] = $this->getIsLike($post['IdUser'], $post['IdPost']);
        $post['IsFavorites'] = $this->getIsFavorites($post['IdUser'], $post['IdPost']);
        $post['TotalLikes'] = $this->getTotalLikes($post['IdPost']);
    }

    // fonction pour récupérer la photo de profile d'un user
    private function processProfilePicture($picture)
    {
        return $picture !== null ? base64_encode($picture) : '';
    }

    // fonction pour récupérer les images lié a un post
    private function getPictures($idPost)
    {
        $stmtPictures = $this->dsn->prepare("SELECT PicturePost FROM PicturePost WHERE IdPost = :IdPost");
        $stmtPictures->bindParam(':IdPost', $idPost);
        $stmtPictures->execute();
        $pictures = $stmtPictures->fetchAll(PDO::FETCH_COLUMN, 0);

        return array_map('base64_encode', $pictures);
    }

    // fonction pour récupérer le nombre totale de like
    private function getTotalLikes($idPost)
    {
        $stmtLikes = $this->dsn->prepare("SELECT COUNT(*) AS TotalLikes FROM LikeFavorites WHERE IdPost = :IdPost AND IsLike = 1");
        $stmtLikes->bindParam(':IdPost', $idPost);
        $stmtLikes->execute();
        return $stmtLikes->fetch(PDO::FETCH_ASSOC)['TotalLikes'];
    }

    // divers fonction lié au like/favoris/commentaire appeler depuis le fichier utils.php
    public function getRelativeTime($date)
    {
        return getRelativeTime($date);
    }

    public function updateViews($idPost)
    {
        return updateViews($this->dsn, $idPost);
    }

    public function getIsLike($IdUser, $IdPost) {
        return getIsLike($this->dsn, $IdUser, $IdPost);
    }

    public function getIsFavorites($IdUser, $IdPost) {
        return getIsFavorites($this->dsn, $IdUser, $IdPost);
    }

    public function LikeData($IdUser, $IdPost) {
        LikeData($this->dsn, $IdUser, $IdPost, "/allPost");
    }

    public function FavoriteData($IdUser, $IdPost) {
        FavoriteData($this->dsn, $IdUser, $IdPost, "/allPost");
    }

    public function getCommentCount($idPost) {
        return getCommentCount($this->dsn, $idPost);
    }
}
