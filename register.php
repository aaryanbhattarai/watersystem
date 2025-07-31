<?php
// register.php
// Handles user registration for the Inventory Demand Forecasting system.

require_once 'functions.php'; // Include necessary functions
require_once 'config.php';   // Include database connection

// Start a secure session
start_secure_session();

// Initialize variables for form inputs and errors
$name = $email = "";
$name_err = $email_err = $password_err = $confirm_password_err = "";
$registration_success = false;

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize and validate name
    $input_name = trim($_POST["name"]);
    if (empty($input_name)) {
        $name_err = "Please enter your name.";
    } else {
        $name = sanitize_input($input_name);
    }

    // Sanitize and validate email
    $input_email = trim($_POST["email"]);
    if (empty($input_email)) {
        $email_err = "Please enter your email.";
    } else {
        // Basic email format validation
        $input_email = sanitize_input($input_email);
        if (!filter_var($input_email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        } else {
            // Check if email already exists in the database
            if (emailExists($conn, $input_email)) {
                $email_err = "This email is already registered. Please use a different email or <a href='login.php'>login</a>.";
            } else {
                $email = $input_email;
            }
        }
    }

    // Validate password
    $password = trim($_POST["password"]);
    if (empty($password)) {
        $password_err = "Please enter a password.";
    } elseif (strlen($password) < 6) {
        $password_err = "Password must have at least 6 characters.";
    }

    // Validate confirm password
    $confirm_password = trim($_POST["confirm_password"]);
    if (empty($confirm_password)) {
        $confirm_password_err = "Please confirm your password.";
    } else {
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

    // Check input errors before inserting in database
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $hashed_password = hash_password($password); // Hash the password
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Registration successful
                $registration_success = true;
                // Clear form inputs
                 $name = $email = "";
                 // Optional: Automatically log the user in after successful registration
                 // $user_id = $conn->insert_id; // Get the ID of the newly inserted user
                 // $_SESSION['user_id'] = $user_id;
                 // $_SESSION['user_name'] = $name;
                 // redirect('dashboard.php');
            } else {
                // Generic error message for user
                $general_err = "Oops! Something went wrong. Please try again later.";
                // Log the actual error for debugging (do not show to user)
                error_log("Error executing registration query: " . $stmt->error);
            }

            // Close statement
            $stmt->close();
        } else {
             $general_err = "Oops! Something went wrong. Please try again later.";
             error_log("Error preparing registration query: " . $conn->error);
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
    <title>Register - Inventory Demand Forecasting</title>
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
        .register-container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .register-container h2 {
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
            background-color: #28a745; /* Green for register */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #218838;
        }
        .error {
            color: #dc3545; /* Red for errors */
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        .success {
             color: #28a745; /* Green for success */
             font-size: 0.875rem;
             margin-top: 0.25rem;
             display: block;
             text-align: center;
             padding: 10px;
             background-color: #d4edda;
             border: 1px solid #c3e6cb;
             border-radius: 4px;
        }
        .login-link {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Register</h2>
    <?php if ($registration_success): ?>
        <div class="success">
            Registration successful! You can now <a href="login.php">log in</a>.
        </div>
    <?php else: ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required>
                <span class="error"><?php echo $name_err; ?></span>
            </div>
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
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
                <span class="error"><?php echo $confirm_password_err; ?></span>
            </div>
            <?php if (!empty($general_err)): ?>
                <div class="error"><?php echo $general_err; ?></div>
            <?php endif; ?>
            <div class="form-group">
                <input type="submit" class="btn" value="Register">
            </div>
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>.
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>