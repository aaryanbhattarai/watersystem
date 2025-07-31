<?php
// dashboard.php
// Main dashboard page for the Inventory Demand Forecasting system.

require_once 'functions.php'; // Include functions for session check, etc.
require_once 'config.php';   // Include database connection if needed later

// Start a secure session
start_secure_session();

// Check if the user is logged in. If not, redirect to login page.
if (!is_logged_in()) {
    redirect('login.php');
}

// Get user information from session (set during login)
$logged_in_user_id = $_SESSION['user_id'];
$logged_in_user_name = $_SESSION['user_name'] ?? 'User'; // Fallback if name not set

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Demand Forecasting</title>
    <!-- Link to your main CSS file -->
    <link rel="stylesheet" href="styles.css">
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
            <!-- Navigation Links -->
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="upload_data.php">Upload Data</a></li>
            <li><a href="results.php">View Results</a></li>
            <li class="logout"><a href="logout.php">Logout (<?php echo htmlspecialchars($logged_in_user_name); ?>)</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($logged_in_user_name); ?>!</h1>
            <p>Use this system to forecast your inventory demand based on historical data and recent sales.</p>
        </div>

        <div class="card-grid"> <!-- Using the grid class from styles.css -->
            <div class="card feature-card">
                <div class="card-body">
                    <h3 class="card-title">Upload Sales Data</h3>
                    <p class="card-text">Upload your most recent monthly sales data (CSV format) to generate forecasts.</p>
                    <a href="upload_data.php" class="btn btn-primary">Upload CSV</a>
                </div>
            </div>

            <div class="card feature-card">
                <div class="card-body">
                    <h3 class="card-title">View Forecasts</h3>
                    <p class="card-text">See the predicted demand for your products for the upcoming month.</p>
                    <a href="results.php" class="btn btn-primary">View Results</a>
                </div>
            </div>

            <div class="card feature-card">
                <div class="card-body">
                    <h3 class="card-title">Compare Models</h3>
                    <p class="card-text">Analyze the performance of Linear Regression and Random Forest models using MAE, RMSE, and R².</p>
                    <a href="results.php" class="btn btn-primary">See Metrics</a>
                </div>
            </div>

            <div class="card feature-card">
                <div class="card-body">
                    <h3 class="card-title">Manage Account</h3>
                    <p class="card-text">View your upload history and account details (future feature).</p>
                    <a href="#" class="btn btn-secondary" onclick="alert('Account management features coming soon!'); return false;">Manage</a>
                </div>
            </div>
        </div>

        <div class="footer-note">
            <p>This system uses machine learning to predict inventory needs. Ensure your uploaded data is accurate for best results.</p>
        </div>
    </div>

</body>
</html>