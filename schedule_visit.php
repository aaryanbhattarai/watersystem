<?php
include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $researcher = mysqli_real_escape_string($conn, $_POST['researcher']);
    
    $sql = "INSERT INTO visits (date, location, researcher) VALUES ('$date', '$location', '$researcher')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p>Visit scheduled successfully!</p>";
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
    <title>Schedule Visit</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Schedule New Field Visit</h2>
    <form method="POST" action="schedule_visit.php">
        <label for="date">Visit Date:</label>
        <input type="date" id="date" name="date" required>

        <label for="location">Location:</label>
        <input type="text" id="location" name="location" required>

        <label for="researcher">Researcher:</label>
        <input type="text" id="researcher" name="researcher" required>

        <input type="submit" value="Schedule Visit">
    </form>
</body>
</html>
