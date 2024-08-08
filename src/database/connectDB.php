<?php

function connectDB()
{
    $dsn = 'mysql:host=mysql;dbname=ifa_database';
    $username = 'ifa_user';
    $password = 'ifa_password';

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
        exit();
    }
}
