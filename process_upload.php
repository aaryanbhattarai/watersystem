<?php
// process_upload.php
// Handles the processing of the uploaded user_upload.csv file by calling the Python script.

require_once 'functions.php'; // Include functions for session check, etc.
// config.php is included via functions.php

// Start a secure session
start_secure_session();

// Check if the user is logged in. If not, redirect to login page.
if (!is_logged_in()) {
    redirect('login.php');
}

// Get user information from session
$logged_in_user_id = $_SESSION['user_id'];
$logged_in_user_name = $_SESSION['user_name'] ?? 'User';

// --- Check if user_upload.csv exists ---
$user_upload_path = 'user_upload.csv';

// Flag to track processing status
$processing_message = '';
$processing_error = false;
$python_output = '';
$python_error_output = '';
$return_code = -1; // Initialize with a default value

if (!file_exists($user_upload_path)) {
    $processing_message = "Error: 'user_upload.csv' not found. Please upload your data first.";
    $processing_error = true;
} else {
    // --- Trigger the Python Forecasting Script ---
    // Ensure the Python script exists
    $forecast_script_path = 'forecast.py';
    
    if (!file_exists($forecast_script_path)) {
         $processing_message = "Error: Forecasting script 'forecast.py' not found on the server.";
         $processing_error = true;
         error_log("Missing forecast.py script at path: $forecast_script_path");
    } else {
        
        // Use PHP's escapeshellcmd and escapeshellarg for basic security
        // Assuming Python is in the system PATH. Adjust 'python' if needed (e.g., 'python3', 'C:\Python\python.exe')
        $command = 'python ' . escapeshellarg($forecast_script_path) . ' 2>&1'; // 2>&1 captures both stdout and stderr
        
        // Execute the command
        // exec() is generally preferred for running scripts and capturing the return code
        // Use $output array to capture the last line, and $full_output to capture all
        exec($command, $full_output, $return_code);
        
        // Combine the full output into a string
        $python_output = implode("\n", $full_output);
        
        // --- Handle the Result of the Python Script Execution ---
        if ($return_code === 0) {
            // Success: Python script ran without errors
            $processing_message = "Data processing and forecasting completed successfully.";
            
            // Optional: Check if forecast_result.json was created
            // if (!file_exists('forecast_result.json')) {
            //     $processing_message .= " Warning: forecast_result.json was not generated.";
            //     $processing_error = true;
            // }
            
        } else {
            // Error: Python script failed
            $processing_message = "Error occurred during data processing or forecasting.";
            $processing_error = true;
            
            // Log the error details for debugging (do not show full details to user in production)
            error_log("Python script 'forecast.py' failed with return code: $return_code");
            error_log("Python script output/errors: " . $python_output);
            
            // Provide a generic message to the user
            // You might choose to show a limited part of $python_output for more specific user feedback
            // during development, but be cautious about exposing internal errors.
        }
    }
}

// After processing (success or failure), redirect to results page
// The results.php page can then check for the existence of forecast_result.json or data in the DB
// and display the appropriate message or results.
// Store the processing message in session to display on results page
$_SESSION['processing_message'] = $processing_message;
$_SESSION['processing_error'] = $processing_error;
if ($processing_error) {
     $_SESSION['python_debug_info'] = "Return Code: $return_code\nOutput:\n" . htmlspecialchars($python_output);
}

// Redirect to results page
redirect('results.php');

?>