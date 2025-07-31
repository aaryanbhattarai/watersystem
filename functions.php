<?php
// functions.php
// This file contains reusable functions for the Inventory Demand Forecasting system.

// Include the database configuration
require_once 'config.php';

/**
 * Starts a secure session.
 * This function ensures sessions are configured securely.
 */
function start_secure_session() {
    // Check if session is already started
    if (session_status() == PHP_SESSION_NONE) {
        // Helps prevent session fixation attacks
        if (ini_set('session.use_only_cookies', 1) === FALSE) {
            die("Could not configure session properly.");
        }

        // Get current cookies params
        $cookieParams = session_get_cookie_params();
        // Set session cookie with security options
        session_set_cookie_params(
            $cookieParams["lifetime"],
            $cookieParams["path"],
            $cookieParams["domain"],
            true,  // Secure (requires HTTPS in production)
            true   // HttpOnly (prevents client-side script access)
        );

        // Starts the session
        session_start();
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
    }
}

/**
 * Checks if a user is currently logged in.
 *
 * @return bool True if user is logged in, false otherwise.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirects the user to a specified page.
 *
 * @param string $location The URL or path to redirect to.
 */
function redirect($location) {
    header("Location: " . $location);
    exit(); // Ensure no further code is executed after redirect
}

/**
 * Sanitizes user input to prevent XSS.
 * Note: For database queries, always use prepared statements in addition to this.
 *
 * @param string $data The user input data.
 * @return string The sanitized data.
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Hashes a password using PHP's default algorithm (currently bcrypt).
 *
 * @param string $password The plain text password.
 * @return string The hashed password.
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifies a plain text password against a hashed password.
 *
 * @param string $password The plain text password.
 * @param string $hash The hashed password from the database.
 * @return bool True if the password matches the hash, false otherwise.
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Saves the forecast results for a specific user and model to the database.
 * This function corresponds to the 'forecasts' table structure.
 *
 * @param mysqli $conn The database connection object (from config.php).
 * @param int $user_id The ID of the user who generated the forecast.
 * @param array $forecast_data An associative array containing forecast details.
 *                            Expected keys: product_id, product_name, category,
 *                            monthly_sales, time_period, price, stock_level,
 *                            promotion, predicted_demand, model_used, mae, rmse, r2_score
 * @return bool True on successful insertion, false on failure.
 */
function saveForecastData($conn, $user_id, $forecast_data) {
    // Prepare the SQL statement to prevent SQL injection
    $sql = "INSERT INTO forecasts (
                user_id, product_id, product_name, category,
                monthly_sales, time_period, price, stock_level, promotion,
                predicted_demand, model_used, mae, rmse, r2_score
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        // i = integer, s = string, d = double/float
        $stmt->bind_param(
            "iisssdsdssssddd",
            $user_id,
            $forecast_data['product_id'],
            $forecast_data['product_name'],
            $forecast_data['category'],
            $forecast_data['monthly_sales'],
            $forecast_data['time_period'],
            $forecast_data['price'],
            $forecast_data['stock_level'],
            $forecast_data['promotion'],
            $forecast_data['predicted_demand'],
            $forecast_data['model_used'], // ENUM: 'Linear Regression' or 'Random Forest'
            $forecast_data['mae'],
            $forecast_data['rmse'],
            $forecast_data['r2_score']
        );

        // Execute the statement
        $result = $stmt->execute();

        // Close the statement
        $stmt->close();

        return $result;
    } else {
        // Log error if preparation fails
        error_log("Error preparing statement for saving forecast: " . $conn->error);
        return false;
    }
}

/**
 * Saves the upload record to the 'uploads' table.
 *
 * @param mysqli $conn The database connection object.
 * @param int $user_id The ID of the user who uploaded the file.
 * @param string $filename The name of the uploaded file.
 * @return bool True on successful insertion, false on failure.
 */
function saveUploadRecord($conn, $user_id, $filename) {
    $sql = "INSERT INTO uploads (user_id, upload_filename) VALUES (?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("is", $user_id, $filename);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } else {
        error_log("Error preparing statement for saving upload record: " . $conn->error);
        return false;
    }
}


/**
 * Checks if a user with a given email already exists in the database.
 *
 * @param mysqli $conn The database connection object.
 * @param string $email The email to check.
 * @return bool True if email exists, false otherwise.
 */
function emailExists($conn, $email) {
    $sql = "SELECT user_id FROM users WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result(); // Needed to use num_rows

        $exists = $stmt->num_rows > 0;

        $stmt->close();
        return $exists;
    }
    return false; // If preparation fails, assume email doesn't exist or handle error
}

// Add more functions here as needed, e.g., for fetching user data, getting upload history, etc.

?>