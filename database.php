<?php
// Database connection (update with your database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "reservoir_monitoring";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>