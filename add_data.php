<?php
include 'db.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $water_level = $_POST['water_level'];
    $temperature = $_POST['temperature'];
    $ph_level = $_POST['ph_level'];
    $turbidity = $_POST['turbidity'];

    // Insert data into the database
    $sql = "INSERT INTO reservoir_data (water_level, temperature, ph_level, turbidity) 
            VALUES ('$water_level', '$temperature', '$ph_level', '$turbidity')";
    
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Redirect back to index page
    header("Location: index.php");
    exit();
}

$conn->close();
?>
