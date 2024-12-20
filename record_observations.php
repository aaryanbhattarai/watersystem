<?php
include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $visit_id = mysqli_real_escape_string($conn, $_POST['visit_id']);
    $observation = mysqli_real_escape_string($conn, $_POST['observation']);

    $sql = "INSERT INTO observations (visit_id, observation) VALUES ('$visit_id', '$observation')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p>Observation recorded successfully!</p>";
    } else {
        echo "<p>Error: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Observation</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Record Observations for Field Visit</h2>
    <form method="POST" action="record_observations.php">
        <label for="visit_id">Visit ID:</label>
        <input type="text" id="visit_id" name="visit_id" required>

        <label for="observation">Observation:</label>
        <textarea id="observation" name="observation" required></textarea>

        <input type="submit" value="Record Observation">
    </form>
</body>
</html>
