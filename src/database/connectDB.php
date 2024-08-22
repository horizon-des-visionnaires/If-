<?php

function connectDB()
{
    $dsn = 'mysql:host=db5016086482.hosting-data.io;dbname=dbs13100519';
    $username = 'dbu4818780';
    $password = 'kw9chr98Xn7ygXV5Fp';

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
        exit();
    }
}
