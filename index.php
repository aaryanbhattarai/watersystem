<?php
include 'db.php';

// Fetch all data from the database
$sql = "SELECT * FROM reservoir_data";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservoir Monitoring System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Reservoir Data Tracking</h1>
        <form action="add_data.php" method="POST">
            <label for="water_level">Water Level (m):</label>
            <input type="number" name="water_level" step="0.01" required>
            
            <label for="temperature">Temperature (°C):</label>
            <input type="number" name="temperature" step="0.01" required>
            
            <label for="ph_level">pH Level:</label>
            <input type="number" name="ph_level" step="0.01" required>
            
            <label for="turbidity">Turbidity (NTU):</label>
            <input type="number" name="turbidity" step="0.01" required>
            
            <input type="submit" value="Add Data">
        </form>

        <h2>Existing Data</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Water Level (m)</th>
                    <th>Temperature (°C)</th>
                    <th>pH Level</th>
                    <th>Turbidity (NTU)</th>
                    <th>Date Recorded</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['water_level']}</td>
                                <td>{$row['temperature']}</td>
                                <td>{$row['ph_level']}</td>
                                <td>{$row['turbidity']}</td>
                                <td>{$row['date_recorded']}</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No data available</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php $conn->close(); ?>
