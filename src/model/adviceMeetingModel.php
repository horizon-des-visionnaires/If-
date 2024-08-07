<?php

namespace adviceMeeting;

use PDO;
use PDOException;

require_once __DIR__ . '/../database/connectDB.php';

class adviceMeetingModel
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

    public function getBuyAdviceData($IdBuyAdvice)
    {
        try {
            $query = "
            SELECT 
                A.IdAdvice,
                A.AdviceType,
                A.AdviceDescription,
                BA.IdBuyAdvice,
                BA.Date AS BuyAdviceDate,
                BA.StartTime AS BuyAdviceStartTime,
                BA.EndTime AS BuyAdviceEndTime,
                U1.IdUser AS SellerId,
                U1.FirstName AS SellerFirstName,
                U1.LastName AS SellerLastName,
                U2.IdUser AS BuyerId,
                U2.FirstName AS BuyerFirstName,
                U2.LastName AS BuyerLastName,
                U1.ProfilPicture AS SellerProfilPicture,
                U2.ProfilPicture AS BuyerProfilPicture
            FROM BuyAdvice BA
            INNER JOIN Advice A ON BA.IdAdvice = A.IdAdvice
            INNER JOIN User U1 ON A.IdUser = U1.IdUser -- Seller
            INNER JOIN User U2 ON BA.IdBuyer = U2.IdUser -- Buyer
            WHERE BA.IdBuyAdvice = :idBuyAdvice
        ";

            $stmt = $this->dsn->prepare($query);
            $stmt->bindParam(':idBuyAdvice', $IdBuyAdvice, PDO::PARAM_INT);
            $stmt->execute();

            $getAdviceData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($getAdviceData === false) {
                echo "No data found for ID: " . htmlspecialchars($IdBuyAdvice);
                return null;
            }

            if ($getAdviceData) {
                $getAdviceData['SellerProfilPicture'] = $getAdviceData['SellerProfilPicture'] ? base64_encode($getAdviceData['SellerProfilPicture']) : '';
                $getAdviceData['BuyerProfilPicture'] = $getAdviceData['BuyerProfilPicture'] ? base64_encode($getAdviceData['BuyerProfilPicture']) : '';
            }

            return $getAdviceData;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    public function getAdviceImages($IdAdvice)
    {
        try {
            $query = "
        SELECT PictureAdvice
        FROM PictureAdvice
        WHERE IdAdvice = :idAdvice
        ";

            $stmt = $this->dsn->prepare($query);
            $stmt->bindParam(':idAdvice', $IdAdvice, PDO::PARAM_INT);
            $stmt->execute();

            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Encode images to base64
            foreach ($images as &$image) {
                $image['PictureAdvice'] = base64_encode($image['PictureAdvice']);
            }

            return $images;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
}
