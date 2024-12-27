<?php
include 'db.php';

// Fetch data for analysis (last 10 entries)
$sql = "SELECT * FROM reservoir_data ORDER BY date_recorded DESC LIMIT 10";
$result = $conn->query($sql);

$data_points = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data_points[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Analysis</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h1>Reservoir Data Analysis</h1>
        
        <canvas id="dataChart"></canvas>
        <script>
            const data = {
                labels: <?php echo json_encode(array_column($data_points, 'date_recorded')); ?>,
                datasets: [{
                    label: 'Water Level',
                    data: <?php echo json_encode(array_column($data_points, 'water_level')); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: false
                }]
            };

            const config = {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Reservoir Water Levels Over Time'
                        }
                    }
                }
            };

            const myChart = new Chart(document.getElementById('dataChart'), config);
        </script>
    </div>
</body>
</html>
