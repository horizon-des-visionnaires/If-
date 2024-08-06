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
        $this->connectDB();
    }

    public function connectDB()
    {
        $this->dsn = new PDO("mysql:host=mysql;dbname=ifa_database", "ifa_user", "ifa_password");
        $this->dsn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function insertAdviceData($AdviceType, $AdviceDescription, $IdUser, $DaysOfWeek, $StartTime, $EndTime)
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

            return true;
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
            // Combine date and time for full DateTime objects
            $start = new \DateTime("$Date $StartTime");
            $end = new \DateTime("$Date $EndTime");
            $interval = $start->diff($end);

            // Vérifier que la durée est bien de 1 heure
            if ($interval->h !== 1 || $interval->i !== 0) {
                echo "Erreur : La durée choisie doit être exactement de 1 heure.";
                return;
            }

            // Vérifier que la réservation est dans le futur
            $now = new \DateTime();
            if ($start < $now) {
                echo "Erreur : Vous ne pouvez pas réserver une date dans le passé.";
                return;
            }

            // Vérifier que le jour choisi est bien renseigné dans le champ DaysOfWeek de la table Advice
            $queryAdvice = "SELECT DaysOfWeek, StartTime, EndTime FROM Advice WHERE IdAdvice = :IdAdvice";
            $stmtAdvice = $this->dsn->prepare($queryAdvice);
            $stmtAdvice->bindParam(':IdAdvice', $IdAdvice);
            $stmtAdvice->execute();
            $adviceData = $stmtAdvice->fetch(PDO::FETCH_ASSOC);

            if (!$adviceData) {
                echo "Erreur : Le conseil sélectionné n'existe pas.";
                return;
            }

            $adviceDaysOfWeek = explode(',', $adviceData['DaysOfWeek']);
            $selectedDayOfWeek = $start->format('l'); // Get the day of the week in English

            if (!in_array($selectedDayOfWeek, $adviceDaysOfWeek)) {
                echo "Erreur : Le jour sélectionné n'est pas disponible pour ce conseil.";
                return;
            }

            // Vérifier que le créneau horaire est couvert par les horaires disponibles
            $adviceStartTime = new \DateTime($adviceData['StartTime']);
            $adviceEndTime = new \DateTime($adviceData['EndTime']);
            $adviceStartTime->setDate($start->format('Y'), $start->format('m'), $start->format('d'));
            $adviceEndTime->setDate($end->format('Y'), $end->format('m'), $end->format('d'));

            if ($start < $adviceStartTime || $end > $adviceEndTime) {
                echo "Erreur : Le créneau horaire choisi n'est pas disponible pour ce conseil.";
                return;
            }

            // Vérifier les chevauchements avec les achats existants pour ce conseil
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
                echo "Erreur : Le créneau horaire choisi est déjà réservé.";
                return;
            }

            // Vérifier si l'utilisateur a déjà une réservation pendant les heures choisies
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
                echo "Erreur : Vous avez déjà une réservation pendant ce créneau horaire.";
                return;
            }

            // Insérer l'achat dans la base de données
            $insertBuyAdviceQuery = "INSERT INTO BuyAdvice (IdAdvice, IdBuyer, Date, StartTime, EndTime)
                                 VALUES (:IdAdvice, :IdBuyer, :Date, :StartTime, :EndTime)";
            $stmtInsertBuyAdvice = $this->dsn->prepare($insertBuyAdviceQuery);
            $stmtInsertBuyAdvice->bindParam(':IdAdvice', $IdAdvice);
            $stmtInsertBuyAdvice->bindParam(':IdBuyer', $IdBuyer);
            $stmtInsertBuyAdvice->bindParam(':Date', $Date);
            $stmtInsertBuyAdvice->bindParam(':StartTime', $StartTime);
            $stmtInsertBuyAdvice->bindParam(':EndTime', $EndTime);
            $stmtInsertBuyAdvice->execute();

            echo "L'achat du conseil a été effectué avec succès.";
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
}
