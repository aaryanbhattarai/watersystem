<?php
// logout.php
// Handles user logout for the Inventory Demand Forecasting system.

require_once 'functions.php'; // Include functions for session management and redirect

// Start the session (or resume existing one)
start_secure_session();

// Unset all session variables
$_SESSION = array();

// Delete the session cookie (if using cookies for sessions)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Redirect to the login page or homepage
redirect('index.php'); // You can change this to 'index.php' if you prefer

?>