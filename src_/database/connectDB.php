<?php
$servername = "localhost";
$username = "ifa_user";
$password = "ifa_password";
$dbname = "ifa_database";
$dsn = '';

try {
    $dsn = 'mysql:host=' . $servername . ';dbname=' . $dbname;
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'connection failed: ' . $e->getMessage();
}
