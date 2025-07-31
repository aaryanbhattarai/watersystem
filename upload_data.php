<?php
// upload_data.php
// Page for users to upload their recent sales data CSV file.

require_once 'functions.php'; // Include functions for session check, etc.
// config.php is included via functions.php

// Start a secure session
start_secure_session();

// Check if the user is logged in. If not, redirect to login page.
if (!is_logged_in()) {
    redirect('login.php');
}

// Initialize variables
$upload_message = '';
$upload_successful = false;

// Get user information from session
$logged_in_user_id = $_SESSION['user_id'];
$logged_in_user_name = $_SESSION['user_name'] ?? 'User';

// Handle file upload on POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"])) {
    // Define upload directory and allowed file types
    $target_dir = "./"; // Upload to the same directory as the script for simplicity (as per project structure)
    // The uploaded file will overwrite user_upload.csv
    $target_file = $target_dir . "user_upload.csv";
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($_FILES["csv_file"]["name"], PATHINFO_EXTENSION));

    // Check if file is a CSV
    if ($fileType != "csv") {
        $upload_message = "Sorry, only CSV files are allowed.";
        $uploadOk = 0;
    }

    // Check file size (e.g., limit to 5MB = 5 * 1024 * 1024 bytes)
    if ($_FILES["csv_file"]["size"] > 5242880) {
        $upload_message = "Sorry, your file is too large (max 5MB).";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $upload_message = "Your file was not uploaded. " . $upload_message;
    // if everything is ok, try to upload file
    } else {
        // Attempt to move the uploaded file to the target location, overwriting the previous one
        if (move_uploaded_file($_FILES["csv_file"]["tmp_name"], $target_file)) {
            // File uploaded successfully
            $upload_successful = true;
            $upload_message = "The file ". htmlspecialchars(basename($_FILES["csv_file"]["name"])). " has been uploaded successfully as 'user_upload.csv'.";
            
            // --- Save Upload Record to Database ---
            // Get the original filename
            $original_filename = basename($_FILES["csv_file"]["name"]);
            if (saveUploadRecord($GLOBALS['conn'], $logged_in_user_id, $original_filename)) {
                 $upload_message .= " Upload record saved.";
            } else {
                 $upload_message .= " Upload record could not be saved. Please try again.";
                 error_log("Failed to save upload record for user_id: $logged_in_user_id, filename: $original_filename");
            }
            
            // --- Trigger Python Forecasting Script ---
            // This is a simplified trigger. In a more robust system, you might use AJAX or a job queue.
            // For now, we'll redirect to a processing page or show a message.
            // Let's redirect to process_upload.php which will handle the Python call and result display
            // redirect('process_upload.php'); // Uncomment this line later when process_upload.php is ready
            
        } else {
            $upload_message = "Sorry, there was an error uploading your file.";
            error_log("File upload error: " . print_r($_FILES["csv_file"], true));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Data - Inventory Demand Forecasting</title>
    <!-- Link to your main CSS file -->
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Basic inline styles for immediate layout, replace with styles.css */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .navbar {
            background-color: #343a40;
            overflow: hidden;
            padding: 0;
            margin: 0;
            color: white;
        }
        .navbar ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
        .navbar li {
            float: left;
        }
        .navbar li a, .navbar li span {
            display: block;
            color: white;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
        }
        .navbar li a:hover {
            background-color: #555;
        }
        .navbar li.logout {
            float: right;
        }
        .main-content {
            padding: 20px;
            max-width: 1200px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .upload-section {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .upload-section h2 {
            text-align: center;
            margin-top: 0;
            color: #333;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: bold;
        }
        .form-group input[type="file"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group input[type="file"]:focus {
             border-color: #007bff;
             outline: none;
        }
        .btn-upload {
            width: 100%;
            padding: 12px;
            background-color: #28a745; /* Green for upload */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-upload:hover {
            background-color: #218838;
        }
        .message {
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .instructions {
            background-color: #e2e3e5;
            border: 1px solid #d6d8db;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        .instructions h3 {
            margin-top: 0;
            color: #495057;
        }
        .instructions ul {
            padding-left: 20px;
        }
        .instructions li {
            margin-bottom: 8px;
        }
        .file-info {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
         /* Clear floats after the navbar */
        .navbar::after {
            content: "";
            display: table;
            clear: both;
        }
        /* Responsive adjustments */
        @media screen and (max-width: 600px) {
            .navbar li {
                float: none;
            }
            .navbar li.logout {
                float: none;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <ul>
            <li><a href="dashboard.php">Inventory Forecast</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="upload_data.php">Upload Data</a></li>
            <li><a href="results.php">View Results</a></li>
            <li class="logout"><a href="logout.php">Logout (<?php echo htmlspecialchars($logged_in_user_name); ?>)</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="upload-section">
            <h2>Upload Recent Sales Data</h2>
            <p>Please upload a CSV file containing your most recent monthly sales data. This data will be used to generate demand forecasts for the upcoming month.</p>

            <?php if (!empty($upload_message)): ?>
                <div class="message <?php echo $upload_successful ? 'success' : 'error'; ?>">
                    <?php echo $upload_message; ?>
                    <?php if ($upload_successful): ?>
                        <p style="margin-top: 10px;"><strong>Next Step:</strong> <a href="process_upload.php">Click here to process the data and generate forecasts.</a></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form id="uploadForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="csv_file">Select CSV File</label>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                    <div class="file-info">File should contain columns: Product_ID, Product_Name, Category, Monthly_Sales, Time, Price ($), Stock_Level, Promotion</div>
                    <div id="fileError" class="error" style="display:none;"></div>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn-upload" value="Upload CSV">
                </div>
            </form>

            <div class="instructions">
                <h3>File Format Instructions</h3>
                <ul>
                    <li>The file <strong>must</strong> be in CSV format.</li>
                    <li>It should contain data for the <strong>most recent month</strong> for which you want to predict the next month's demand.</li>
                    <li>Required columns (in order):
                        <ul>
                            <li><code>Product_ID</code> (Integer)</li>
                            <li><code>Product_Name</code> (String)</li>
                            <li><code>Category</code> (String)</li>
                            <li><code>Monthly_Sales</code> (Integer)</li>
                            <li><code>Time</code> (YYYY-MM format, e.g., 2025-07)</li>
                            <li><code>Price ($)</code> (Numeric)</li>
                            <li><code>Stock_Level</code> (Integer)</li>
                            <li><code>Promotion</code> (Yes/No)</li>
                        </ul>
                    </li>
                    <li>Ensure <code>Stock_Level >= Monthly_Sales</code> for each row.</li>
                    <li>Example row: <code>75,Smart Bulb,Electronics,35,2025-07,19,40,No</code></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Link to the form validation JavaScript -->
    <script src="form-validation.js"></script>

</body>
</html>