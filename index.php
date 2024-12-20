<?php
// Include the database connection file
include 'database.php';

// Fetch reservoir data from the database
$query = "SELECT name, level, capacity FROM reservoirs ORDER BY name";
$result = mysqli_query($conn, $query);

// Prepare data for the frontend
$reservoirData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reservoirData[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservoir Monitoring Dashboard</title>
    <link rel="stylesheet" href="3styles.css">
</head>
<body>
    <header>
        <h1>Reservoir Monitoring Dashboard</h1>
    </header>

    <section class="dashboard">
        <div class="container">
            <h2>Reservoir Levels and Capacities</h2>
            <div class="chart">
                <!-- Loop through the data and display each reservoir as a bar -->
                <?php foreach ($reservoirData as $reservoir): ?>
                    <div class="bar-container">
                        <div 
                            class="bar level" 
                            style="height: <?php echo ($reservoir['level'] / $reservoir['capacity']) * 100; ?>%;"
                            title="Level: <?php echo $reservoir['level']; ?> units">
                        </div>
                        <div 
                            class="bar capacity" 
                            style="height: 100%;"
                            title="Capacity: <?php echo $reservoir['capacity']; ?> units">
                        </div>
                        <p><?php echo htmlspecialchars($reservoir['name']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer>
        <p>© 2024 Reservoir Monitoring Project</p>
    </footer>
</body>
</html>
