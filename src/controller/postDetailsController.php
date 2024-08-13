<?php

namespace postDetails;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require 'vendor/autoload.php';

require_once __DIR__ . '/../model/postDetailsModel.php';

class postDetailsController
{
    protected $twig;
    private $loader;
    private $postDetailsModel;
    private $notificationModel;

    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/../views/templates');
        $this->twig = new Environment($this->loader);
        $this->postDetailsModel = new postDetailsModel();
        $this->notificationModel = new \notification\notificationModel();
    }

    public function post($idPost)
    {
        session_start();

        $isConnected = false;
        $userId = null;
        $user = null;
        $isAdmin = null;
        if (isset($_SESSION['IdUser'])) {
            $isConnected = true;
            $userId = $_SESSION['IdUser'];
            $userModel = new \profile\profileModel();
            $user = $userModel->getUserById($userId);
            $isAdmin = $_SESSION['IsAdmin'];
        }

        $IsAdmin = false;
        if (isset($_SESSION['IsAdmin']) && $_SESSION['IsAdmin'] == 1) {
            $IsAdmin = true;
        }

        $postData = $this->postDetailsModel->getPost($idPost);
        $commentsData = $this->postDetailsModel->getComment($idPost);
        $commentCount = $this->postDetailsModel->getCommentCount($idPost);

        foreach ($postData as &$post) {
            $post['IsLike'] = $this->postDetailsModel->getIsLike($userId, $post['IdPost']);
            $post['IsFavorites'] = $this->postDetailsModel->getIsFavorites($userId, $post['IdPost']);
        }

        if (!empty($postData)) {
            $firstName = $postData[0]['FirstName'];
            $lastName = $postData[0]['LastName'];
            $isPro = $postData[0]['IsPro'];
        } else {
            $firstName = '';
            $lastName = '';
            $isPro = '';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId !== null) {
            $this->addCommentData($idPost);
        }

        $this->getDeletePostData();
        $this->getDeleteCommentData();
        $this->getDataAddLike();
        $this->getDataAddFavorite();

        $unreadCount = $this->notificationModel->getUnreadNotificationCount($userId);

        $this->getUpdatePostData();

        echo $this->twig->render('postDetails/postDetails.html.twig', [
            'isConnected' => $isConnected,
            'userId' => $userId,
            'IsAdmin' => $IsAdmin,
            'postData' => $postData,
            'firstName' => $firstName,
            'isPro' => $isPro,
            'lastName' => $lastName,
            'commentsData' => $commentsData,
            'commentCount' => $commentCount,
            'user' => $user,
            'unreadCount' => $unreadCount,
            'isAdmin' => $isAdmin
        ]);
    }

    public function addCommentData($idPost)
    {
        if (isset($_POST['addComment'])) {
            $ContentComment = $_POST['ContentComment'];
            $IdUser = $_POST['IdUser'];

            $IdComment = $this->postDetailsModel->addComment($idPost, $ContentComment, $IdUser);

            if ($IdComment) {
                header("Location: /postDetails-$idPost");
                exit();
            }
        }
    }

    public function getDeletePostData()
    {
        if (isset($_POST['deletePost'])) {
            $idPost = $_POST['idPost'];
            $idUser = $_SESSION['IdUser'];
            $this->postDetailsModel->deletePost($idPost, $idUser);
        }
    }

    public function getDeleteCommentData()
    {
        if (isset($_POST['deletePostComment'])) {
            $idComment = $_POST['idComment'];
            $idPost = $_POST['idPost'];
            $this->postDetailsModel->deleteComment($idComment, $idPost);
        }
    }

    public function getDataAddLike()
    {
        if (isset($_POST['AddLike'])) {
            if (isset($_SESSION['IdUser'])) {
                $IdUser = $_SESSION['IdUser'];
                $IdPost = $_POST['IdPost'];
                $this->postDetailsModel->LikeData($IdUser, $IdPost);
            }
        }
    }

    public function getDataAddFavorite()
    {
        if (isset($_POST['AddFavorite'])) {
            if (isset($_SESSION['IdUser'])) {
                $IdUser = $_SESSION['IdUser'];
                $IdPost = $_POST['IdPost'];
                $this->postDetailsModel->FavoriteData($IdUser, $IdPost);
            }
        }
    }

    public function getUpdatePostData()
    {
        if (isset($_POST['updatePost'])) {
            $TitlePost = $_POST['TitlePost'] ?? null;
            $ContentPost = $_POST['ContentPost'] ?? null;
            $IdUser = $_POST['IdUser'];
            $IdPost = $_POST['IdPost'];

            $PicturesPost = [];
            if (isset($_FILES["PicturePost"])) {
                if (count($_FILES["PicturePost"]["tmp_name"]) > 6) {
                    echo "You can upload a maximum of 6 images.";
                    return;
                }
                foreach ($_FILES["PicturePost"]["tmp_name"] as $tmpName) {
                    if ($tmpName) {
                        $PicturesPost[] = file_get_contents($tmpName);
                    }
                }
            }

            $this->postDetailsModel->updatePostData($TitlePost, $ContentPost, $IdUser, $IdPost, $PicturesPost);
        }
    }
}
