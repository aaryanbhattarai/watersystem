<?php
include 'db.php';

// Fetch all historical data
$sql = "SELECT * FROM reservoir_data ORDER BY date_recorded DESC";
$result = $conn->query($sql);

$data_history = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data_history[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservoir History</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Reservoir History</h1>
        
        <?php if (count($data_history) > 0): ?>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Water Level</th>
                    <th>Temperature</th>
                    <th>pH Level</th>
                    <th>Turbidity</th>
                </tr>
                <?php foreach ($data_history as $data): ?>
                    <tr>
                        <td><?php echo $data['date_recorded']; ?></td>
                        <td><?php echo $data['water_level']; ?></td>
                        <td><?php echo $data['temperature']; ?></td>
                        <td><?php echo $data['ph_level']; ?></td>
                        <td><?php echo $data['turbidity']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No history data available.</p>
        <?php endif; ?>
    </div>
</body>
</html>
