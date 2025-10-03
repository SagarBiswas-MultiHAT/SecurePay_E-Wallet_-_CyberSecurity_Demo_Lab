<?php
// db_connect.php
// This file handles the database connection using MySQLi
// Update the credentials as per your local MySQL setup

$host = 'localhost';
$db   = 'securepay_db';
$user = 'root';
$pass = '';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
// Connection successful
