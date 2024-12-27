<?php
include 'db.php';

// Fetch the most recent data from the database
$sql = "SELECT * FROM reservoir_data ORDER BY date_recorded DESC LIMIT 1";
$result = $conn->query($sql);

$current_data = null;
if ($result->num_rows > 0) {
    $current_data = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Data View</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Live Reservoir Monitoring Data</h1>
        
        <?php if ($current_data): ?>
            <p><strong>Water Level (m):</strong> <?php echo $current_data['water_level']; ?></p>
            <p><strong>Temperature (°C):</strong> <?php echo $current_data['temperature']; ?></p>
            <p><strong>pH Level:</strong> <?php echo $current_data['ph_level']; ?></p>
            <p><strong>Turbidity (NTU):</strong> <?php echo $current_data['turbidity']; ?></p>
            <p><strong>Date Recorded:</strong> <?php echo $current_data['date_recorded']; ?></p>
        <?php else: ?>
            <p>No data available at the moment.</p>
        <?php endif; ?>
    </div>
</body>
</html>
