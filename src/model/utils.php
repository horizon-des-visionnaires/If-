<?php

function getIsLike(PDO $dsn, $IdUser, $IdPost)
{
    try {
        $stmt = $dsn->prepare(
            "SELECT IsLike 
            FROM LikeFavorites 
            WHERE IdUser = :IdUser AND IdPost = :IdPost"
        );
        $stmt->bindParam(':IdUser', $IdUser, PDO::PARAM_INT);
        $stmt->bindParam(':IdPost', $IdPost, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false ? (bool)$result['IsLike'] : false;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

function getIsFavorites(PDO $dsn, $IdUser, $IdPost)
{
    try {
        $stmt = $dsn->prepare(
            "SELECT IsFavorites 
            FROM LikeFavorites 
            WHERE IdUser = :IdUser AND IdPost = :IdPost"
        );
        $stmt->bindParam(':IdUser', $IdUser, PDO::PARAM_INT);
        $stmt->bindParam(':IdPost', $IdPost, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false ? (bool)$result['IsFavorites'] : false;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

function LikeData(PDO $dsn, $IdUser, $IdPost, $redirectUrl)
{
    try {
        $checkIsLike = "SELECT COUNT(*) FROM LikeFavorites WHERE IdUser = :IdUser AND IdPost = :IdPost";
        $execCheckIsLike = $dsn->prepare($checkIsLike);
        $execCheckIsLike->bindParam(':IdUser', $IdUser);
        $execCheckIsLike->bindParam(':IdPost', $IdPost);
        $execCheckIsLike->execute();

        $isLiked = $execCheckIsLike->fetchColumn() > 0;

        if ($isLiked) {
            $updateLike = "UPDATE LikeFavorites SET IsLike = NOT IsLike WHERE IdUser = :IdUser AND IdPost = :IdPost";
        } else {
            $updateLike = "INSERT INTO LikeFavorites (IdUser, IdPost, IsLike) VALUES (:IdUser, :IdPost, 1)";
        }

        $execUpdateLike = $dsn->prepare($updateLike);
        $execUpdateLike->bindParam(':IdUser', $IdUser);
        $execUpdateLike->bindParam(':IdPost', $IdPost);

        if ($execUpdateLike->execute()) {
            header("Location: " . $redirectUrl);
            exit();
        } else {
            echo "Erreur lors de l'ajout ou de la suppression du like.";
        }
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

function FavoriteData(PDO $dsn, $IdUser, $IdPost, $redirectUrl)
{
    try {
        $checkFavorite = "SELECT IsFavorites FROM LikeFavorites WHERE IdUser = :IdUser AND IdPost = :IdPost";
        $stmt = $dsn->prepare($checkFavorite);
        $stmt->bindParam(':IdUser', $IdUser);
        $stmt->bindParam(':IdPost', $IdPost);
        $stmt->execute();

        $existingFavorite = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingFavorite) {
            $updateFavorite = "UPDATE LikeFavorites SET IsFavorites = NOT IsFavorites WHERE IdUser = :IdUser AND IdPost = :IdPost";
        } else {
            $updateFavorite = "INSERT INTO LikeFavorites (IdUser, IdPost, IsFavorites) VALUES (:IdUser, :IdPost, 1)";
        }

        $execUpdateFavorite = $dsn->prepare($updateFavorite);
        $execUpdateFavorite->bindParam(':IdUser', $IdUser);
        $execUpdateFavorite->bindParam(':IdPost', $IdPost);

        if ($execUpdateFavorite->execute()) {
            header("Location: " . $redirectUrl);
            exit();
        } else {
            echo "Erreur lors de l'ajout ou de la suppression du favori.";
        }
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

function getCommentCount(PDO $dsn, $idPost)
{
    try {
        $stmt = $dsn->prepare(
            "SELECT COUNT(*) AS CommentCount 
        FROM Comment 
        WHERE IdPost = :idPost"
        );
        $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $result['CommentCount'];
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return 0;
    }
}

function updateViews(PDO $dsn, $idPost)
{
    try {
        $stmt = $dsn->prepare("UPDATE Post SET Views = Views + 1 WHERE IdPost = :IdPost");
        $stmt->bindParam(':IdPost', $idPost);

        if ($stmt->execute()) {
            header("Location: /postDetails-$idPost");
            exit();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

function deletePost(PDO $dsn, $idPost, $idUser)
{
    try {
        $dsn->beginTransaction();

        $deletePicturePost = "DELETE FROM PicturePost WHERE IdPost = :IdPost";
        $picturePost = $dsn->prepare($deletePicturePost);
        $picturePost->bindParam(':IdPost', $idPost);
        $picturePost->execute();

        $deleteComment = "DELETE FROM Comment WHERE IdPost = :IdPost";
        $comment = $dsn->prepare($deleteComment);
        $comment->bindParam(':IdPost', $idPost);
        $comment->execute();

        $deleteLike = "DELETE FROM `LikeFavorites` WHERE IdPost = :IdPost";
        $like = $dsn->prepare($deleteLike);
        $like->bindParam(':IdPost', $idPost);
        $like->execute();

        $deletePost = "DELETE FROM Post WHERE IdPost = :IdPost";
        $stmt = $dsn->prepare($deletePost);
        $stmt->bindParam(':IdPost', $idPost);
        $stmt->execute();

        $dsn->commit();

        header("Location: /profile-$idUser");
        exit();
    } catch (PDOException $e) {
        $dsn->rollBack();
        $error = "error: " . $e->getMessage();
        echo $error;
    }
}

function getRelativeTime($date)
{
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return $diff . ' s';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' m';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' h';
    } else {
        return floor($diff / 86400) . ' J';
    }
}
