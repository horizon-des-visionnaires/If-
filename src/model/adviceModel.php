<?php

namespace advice;

use PDO;
use PDOException;

require_once 'database/connectDB.php';

class adviceModel
{
    private $dsn;

    public function __construct()
    {
        $this->dsn = connectDB();
    }

    public function insertAdviceData($AdviceType, $AdviceDescription, $IdUser, $DaysOfWeek, $StartTime, $EndTime, $PictureAdvice)
    {
        try {
            $countAdviceQuery = "SELECT COUNT(*) FROM Advice WHERE IdUser = :IdUser";
            $stmtCount = $this->dsn->prepare($countAdviceQuery);
            $stmtCount->bindParam(':IdUser', $IdUser);
            $stmtCount->execute();
            $currentAdviceCount = $stmtCount->fetchColumn();

            if ($currentAdviceCount >= 3) {
                return "L'utilisateur a déjà trois conseils, impossible d'en ajouter d'autres.";
            }

            $insertAdviceQuery = "INSERT INTO Advice (AdviceType, AdviceDescription, IdUser, DaysOfWeek, StartTime, EndTime)
            VALUES (:AdviceType, :AdviceDescription, :IdUser, :DaysOfWeek, :StartTime, :EndTime)";
            $execInsertAdvice = $this->dsn->prepare($insertAdviceQuery);
            $execInsertAdvice->bindParam(':AdviceType', $AdviceType);
            $execInsertAdvice->bindParam(':AdviceDescription', $AdviceDescription);
            $execInsertAdvice->bindParam(':IdUser', $IdUser);
            $execInsertAdvice->bindParam(':DaysOfWeek', $DaysOfWeek);
            $execInsertAdvice->bindParam(':StartTime', $StartTime);
            $execInsertAdvice->bindParam(':EndTime', $EndTime);
            $execInsertAdvice->execute();

            $IdAdvice = $this->dsn->lastInsertId();
            $stmt = $this->dsn->prepare("INSERT INTO PictureAdvice (IdAdvice, PictureAdvice) VALUES (:IdAdvice, :PictureAdvice)");
            foreach ($PictureAdvice as $PictureAdvice) {
                $stmt->bindParam(':IdAdvice', $IdAdvice);
                $stmt->bindParam(':PictureAdvice', $PictureAdvice, PDO::PARAM_LOB);
                $stmt->execute();
            }

            header('Location: /advice');
            exit();
        } catch (PDOException $e) {
            return "Erreur : " . $e->getMessage();
        }
    }

    public function getAdviceAndUserInfo()
    {
        try {
            $query = "SELECT a.AdviceType, a.IdAdvice, a.AdviceDescription, a.DaysOfWeek, a.StartTime, a.EndTime, p.IdUser, p.FirstName, p.LastName, p.ProfilPicture, p.ProfilPromotion
                  FROM Advice a
                  JOIN User p ON a.IdUser = p.IdUser";

            $stmt = $this->dsn->prepare($query);
            $stmt->execute();
            $userData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($userData as &$user) {
                if (isset($user['ProfilPicture']) && $user['ProfilPicture'] !== null) {
                    $user['ProfilPicture'] = base64_encode($user['ProfilPicture']);
                } else {
                    $user['ProfilPicture'] = '';
                }

                $stmtPictures = $this->dsn->prepare("SELECT PictureAdvice FROM PictureAdvice WHERE IdAdvice = :IdAdvice");
                $stmtPictures->bindParam(':IdAdvice', $user['IdAdvice']);
                $stmtPictures->execute();
                $pictures = $stmtPictures->fetchAll(PDO::FETCH_COLUMN);
                $user['PicturesAdvice'] = array_map('base64_encode', $pictures);
            }

            return $userData;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    public function getFilteredAdvice($searchQuery = '', $sortBy = '', $order = 'DESC')
    {
        $query = $this->buildAdviceQuery($searchQuery, $sortBy, $order);
        $stmt = $this->dsn->prepare($query);

        if ($searchQuery) {
            $searchQuery = "%{$searchQuery}%";
            $stmt->bindParam(':searchQuery', $searchQuery);
        }

        $stmt->execute();
        $adviceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($adviceData as &$advice) {
            if (isset($advice['ProfilPicture']) && $advice['ProfilPicture'] !== null) {
                $advice['ProfilPicture'] = base64_encode($advice['ProfilPicture']);
            } else {
                $advice['ProfilPicture'] = '';
            }

            // Récupérer les images des conseils
            $stmtPictures = $this->dsn->prepare("SELECT PictureAdvice FROM PictureAdvice WHERE IdAdvice = :IdAdvice");
            $stmtPictures->bindParam(':IdAdvice', $advice['IdAdvice']);
            $stmtPictures->execute();
            $pictures = $stmtPictures->fetchAll(PDO::FETCH_COLUMN);
            $advice['PicturesAdvice'] = array_map('base64_encode', $pictures);
        }

        return $adviceData;
    }

    private function buildAdviceQuery($searchQuery, $sortBy, $order)
    {
        $query = "SELECT a.AdviceType, a.IdAdvice, a.AdviceDescription, a.CreatedAt, a.DaysOfWeek, a.StartTime, a.EndTime, p.IdUser, p.FirstName, p.LastName, p.ProfilPicture, p.ProfilPromotion 
                  FROM Advice a
                  JOIN User p ON a.IdUser = p.IdUser";

        if ($searchQuery) {
            $query .= " WHERE (a.AdviceType LIKE :searchQuery 
                    OR a.AdviceDescription LIKE :searchQuery
                    OR p.FirstName LIKE :searchQuery 
                    OR p.LastName LIKE :searchQuery)";
        }

        if ($sortBy) {
            switch ($sortBy) {
                case 'type':
                    $query .= " ORDER BY a.AdviceType $order";
                    break;
                case 'user':
                    $query .= " ORDER BY p.FirstName $order, p.LastName $order";
                    break;
                default:
                    $query .= " ORDER BY a.CreatedAt $order";
                    break;
            }
        } else {
            $query .= " ORDER BY a.CreatedAt $order";
        }

        return $query;
    }

    public function buyAdvice($Date, $StartTime, $EndTime, $IdAdvice, $IdBuyer)
    {
        try {
            // Vérifier la validité de la réservation
            $this->validateReservation($Date, $StartTime, $EndTime);

            // Vérifier les disponibilités du conseil
            $adviceData = $this->getAdviceData($IdAdvice);
            $this->checkAdviceAvailability($adviceData, $Date, $StartTime, $EndTime);

            // Vérifier les chevauchements de réservation
            $this->checkOverlappingReservations($IdAdvice, $Date, $StartTime, $EndTime, $IdBuyer);

            // Effectuer la réservation
            $this->insertBuyAdvice($IdAdvice, $IdBuyer, $Date, $StartTime, $EndTime);

            // Envoyer la notification
            $this->sendNotification($adviceData, $IdBuyer, $Date, $StartTime, $EndTime);

            echo "L'achat du conseil a été effectué avec succès.";
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    private function validateReservation($Date, $StartTime, $EndTime)
    {
        $start = new \DateTime("$Date $StartTime");
        $end = new \DateTime("$Date $EndTime");
        $interval = $start->diff($end);

        // Vérifier que la durée est bien de 1 heure
        if ($interval->h !== 1 || $interval->i !== 0) {
            echo ("Erreur : La durée choisie doit être exactement de 1 heure.");
        }

        // Vérifier que la réservation est dans le futur
        $endOfToday = (new \DateTime())->setTime(23, 59, 59);
        if ($start <= $endOfToday) {
            echo("Erreur : Vous ne pouvez pas réserver un conseil pour le jour même.");
        }
    }

    private function getAdviceData($IdAdvice)
    {
        $queryAdvice = "SELECT DaysOfWeek, StartTime, EndTime, IdUser FROM Advice WHERE IdAdvice = :IdAdvice";
        $stmtAdvice = $this->dsn->prepare($queryAdvice);
        $stmtAdvice->bindParam(':IdAdvice', $IdAdvice);
        $stmtAdvice->execute();
        $adviceData = $stmtAdvice->fetch(PDO::FETCH_ASSOC);

        if (!$adviceData) {
            echo("Erreur : Le conseil sélectionné n'existe pas.");
        }

        return $adviceData;
    }

    private function checkAdviceAvailability($adviceData, $Date, $StartTime, $EndTime)
    {
        $start = new \DateTime("$Date $StartTime");
        $end = new \DateTime("$Date $EndTime");

        $adviceDaysOfWeek = explode(',', $adviceData['DaysOfWeek']);
        $selectedDayOfWeek = $start->format('l'); // Get the day of the week in English

        if (!in_array($selectedDayOfWeek, $adviceDaysOfWeek)) {
            echo("Erreur : Le jour sélectionné n'est pas disponible pour ce conseil.");
        }

        $adviceStartTime = new \DateTime($adviceData['StartTime']);
        $adviceEndTime = new \DateTime($adviceData['EndTime']);
        $adviceStartTime->setDate($start->format('Y'), $start->format('m'), $start->format('d'));
        $adviceEndTime->setDate($end->format('Y'), $end->format('m'), $end->format('d'));

        if ($start < $adviceStartTime || $end > $adviceEndTime) {
            echo("Erreur : Le créneau horaire choisi n'est pas disponible pour ce conseil.");
        }
    }

    private function checkOverlappingReservations($IdAdvice, $Date, $StartTime, $EndTime, $IdBuyer)
    {
        $queryOverlap = "SELECT COUNT(*) FROM BuyAdvice
                         WHERE IdAdvice = :IdAdvice
                         AND Date = :Date
                         AND (StartTime < :EndTime AND EndTime > :StartTime)";
        $stmtOverlap = $this->dsn->prepare($queryOverlap);
        $stmtOverlap->bindParam(':IdAdvice', $IdAdvice);
        $stmtOverlap->bindParam(':Date', $Date);
        $stmtOverlap->bindParam(':StartTime', $StartTime);
        $stmtOverlap->bindParam(':EndTime', $EndTime);
        $stmtOverlap->execute();
        $overlapCount = $stmtOverlap->fetchColumn();

        if ($overlapCount > 0) {
            echo("Erreur : Le créneau horaire choisi est déjà réservé.");
        }

        $queryUserOverlap = "SELECT COUNT(*) FROM BuyAdvice
                             WHERE IdBuyer = :IdBuyer
                             AND Date = :Date
                             AND (StartTime < :EndTime AND EndTime > :StartTime)";
        $stmtUserOverlap = $this->dsn->prepare($queryUserOverlap);
        $stmtUserOverlap->bindParam(':IdBuyer', $IdBuyer);
        $stmtUserOverlap->bindParam(':Date', $Date);
        $stmtUserOverlap->bindParam(':StartTime', $StartTime);
        $stmtUserOverlap->bindParam(':EndTime', $EndTime);
        $stmtUserOverlap->execute();
        $userOverlapCount = $stmtUserOverlap->fetchColumn();

        if ($userOverlapCount > 0) {
            echo("Erreur : Vous avez déjà une réservation pendant ce créneau horaire.");
        }
    }

    private function insertBuyAdvice($IdAdvice, $IdBuyer, $Date, $StartTime, $EndTime)
    {
        $insertBuyAdviceQuery = "INSERT INTO BuyAdvice (IdAdvice, IdBuyer, Date, StartTime, EndTime)
                                 VALUES (:IdAdvice, :IdBuyer, :Date, :StartTime, :EndTime)";
        $stmtInsertBuyAdvice = $this->dsn->prepare($insertBuyAdviceQuery);
        $stmtInsertBuyAdvice->bindParam(':IdAdvice', $IdAdvice);
        $stmtInsertBuyAdvice->bindParam(':IdBuyer', $IdBuyer);
        $stmtInsertBuyAdvice->bindParam(':Date', $Date);
        $stmtInsertBuyAdvice->bindParam(':StartTime', $StartTime);
        $stmtInsertBuyAdvice->bindParam(':EndTime', $EndTime);
        $stmtInsertBuyAdvice->execute();
    }

    private function sendNotification($adviceData, $IdBuyer, $Date, $StartTime, $EndTime)
    {
        // Récupérer les informations du vendeur
        $querySellerInfo = "SELECT FirstName, LastName FROM User WHERE IdUser = :IdUser";
        $stmtSellerInfo = $this->dsn->prepare($querySellerInfo);
        $stmtSellerInfo->bindParam(':IdUser', $adviceData['IdUser']);
        $stmtSellerInfo->execute();
        $sellerInfo = $stmtSellerInfo->fetch(PDO::FETCH_ASSOC);

        if (!$sellerInfo) {
            echo("Erreur : Impossible de trouver l'utilisateur qui a proposé ce conseil.");
        }

        // Récupérer les informations de l'acheteur pour la notification
        $queryBuyerInfo = "SELECT FirstName, LastName FROM User WHERE IdUser = :IdBuyer";
        $stmtBuyerInfo = $this->dsn->prepare($queryBuyerInfo);
        $stmtBuyerInfo->bindParam(':IdBuyer', $IdBuyer);
        $stmtBuyerInfo->execute();
        $buyerInfo = $stmtBuyerInfo->fetch(PDO::FETCH_ASSOC);

        if (!$buyerInfo) {
            echo("Erreur : Impossible de trouver l'utilisateur acheteur.");
        }

        // Créer le message de notification
        $MessageNotif = "{$buyerInfo['FirstName']} {$buyerInfo['LastName']} a acheté votre conseil pour le {$Date} entre {$StartTime} et {$EndTime}.";

        // Insérer la notification pour le vendeur
        $insertNotificationQuery = "INSERT INTO Notifications (IdUser, MessageNotif) VALUES (:IdUser, :MessageNotif)";
        $stmtInsertNotification = $this->dsn->prepare($insertNotificationQuery);
        $stmtInsertNotification->bindParam(':IdUser', $adviceData['IdUser']);  // Le vendeur reçoit la notification
        $stmtInsertNotification->bindParam(':MessageNotif', $MessageNotif);
        $stmtInsertNotification->execute();
    }
}
