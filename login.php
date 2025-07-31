<?php
// login.php
// Handles user login for the Inventory Demand Forecasting system.

require_once 'functions.php'; // Include necessary functions
require_once 'config.php';   // Include database connection

// Start a secure session
start_secure_session();

// Initialize variables
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if email is empty
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = sanitize_input(trim($_POST["email"]));
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($email_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT user_id, name, email, password_hash FROM users WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $email);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();

                // Check if email exists, if yes then verify password
                if ($stmt->num_rows == 1) {
                    // Bind result variables
                    $stmt->bind_result($user_id, $name, $email, $hashed_password);
                    if ($stmt->fetch()) {
                        if (verify_password($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            // Session is already started securely by start_secure_session()

                            // Store data in session variables
                            $_SESSION["user_id"] = $user_id;
                            $_SESSION["user_name"] = $name; // Store name for easy access

                            // Redirect user to dashboard page
                            redirect('dashboard.php');
                        } else {
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    // Email doesn't exist, display a generic error message
                    $login_err = "Invalid email or password.";
                }
            } else {
                $login_err = "Oops! Something went wrong. Please try again later.";
                error_log("Error executing login query: " . $stmt->error);
            }

            // Close statement
            $stmt->close();
        } else {
             $login_err = "Oops! Something went wrong. Please try again later.";
             error_log("Error preparing login query: " . $conn->error);
        }
    }

    // Close connection
    // $conn->close(); // Optional: Close here or let it close at script end
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Demand Forecasting</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            margin-top: 0;
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
         .form-group input:focus {
             border-color: #007bff;
             outline: none;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff; /* Blue for login */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error {
            color: #dc3545; /* Red for errors */
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        .register-link {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <?php if (!empty($login_err)): ?>
        <div class="error"><?php echo $login_err; ?></div>
    <?php endif; ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
            <span class="error"><?php echo $email_err; ?></span>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
            <span class="error"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn" value="Login">
        </div>
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>.
        </div>
    </form>
</div>

</body>
</html>