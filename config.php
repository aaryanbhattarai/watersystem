<?php
// config.php
// This file handles the database connection for the Inventory Demand Forecasting system.

// Database configuration - Adjust these if your XAMPP setup is different
define('DB_HOST', 'localhost');  // Default for XAMPP
define('DB_USER', 'root');       // Default XAMPP MySQL user
define('DB_PASS', '');           // Default XAMPP MySQL password (usually empty)
define('DB_NAME', 'inventory_db'); // Database name as per project description

// Create connection using MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // It's better to log the specific error for debugging, but not show it to users in production
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("Connection failed. Please check the system configuration."); // User-friendly message
}

// Optional: Set charset to UTF-8 for better compatibility
$conn->set_charset("utf8");

// Note: The $conn variable is now available globally in scripts that include this file.
// Example usage in other files:
// require_once 'config.php';
// $sql = "SELECT * FROM users";
// $result = $conn->query($sql);

?>