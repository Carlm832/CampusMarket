<?php
// config/db.php
// Central Database Connection using PDO

$host = 'localhost';
$db   = 'campusmarket';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // If the database doesn't exist, we might want to catch and handle or just fail
     die("Database connection failed: " . $e->getMessage());
}

// Global accessor for the database
return $pdo;
