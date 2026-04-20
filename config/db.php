<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "campusmarket";

// Create connection (without database first)
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
if (!$conn->select_db($dbname)) {
    die("Error selecting database: " . $conn->error);
}

// Set charset
$conn->set_charset("utf8");
// $conn is available if you add SQL-backed features later.
