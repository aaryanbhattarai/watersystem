<?php
// index.php
// Landing page for the Inventory Demand Forecasting system.

require_once 'functions.php'; // Include functions for session and redirect

// Start a secure session
start_secure_session();

// Check if the user is already logged in
if (is_logged_in()) {
    // If logged in, redirect to the dashboard
    redirect('dashboard.php');
}
// If not logged in, the HTML below will be shown
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Demand Forecasting - Welcome</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
    <!-- No inline styles needed as they are in styles.css now -->
</head>
<body>

<!-- Navigation Bar with Logo -->
<nav class="navbar">
    <ul>
        <!-- Logo and Brand -->
        <li class="navbar-brand">
            <img src="logo.jpg" alt="Inventory Forecast Logo" class="navbar-logo">
            <span class="brand-text">Inventory Forecast</span>
        </li>
        <!-- Navigation Links (if any for non-logged-in users, e.g., about) -->
        <!-- For index, main actions are Login/Register buttons below -->
    </ul>
</nav>

<div class="main-content">
    <div class="welcome-section">
        <h1>Welcome to Inventory Demand Forecasting</h1>
        <p>Predict your inventory needs with machine learning.</p>
        <p>Please log in or register to continue.</p>
        <a href="login.php" class="btn btn-primary">Login</a>
        <a href="register.php" class="btn btn-secondary">Register</a>
    </div>
</div>

</body>
</html>