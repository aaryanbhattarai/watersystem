<?php
include('database.php');

$reports = [];
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['filter'])) {
    $filter = mysqli_real_escape_string($conn, $_GET['filter']);
    
    $sql = "SELECT * FROM visits WHERE researcher LIKE '%$filter%'";
    $result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $reports[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Report</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Generate Field Research Report</h2>
    
    <form method="GET" action="report_generator.php">
        <label for="filter">Filter by Researcher:</label>
        <input type="text" id="filter" name="filter" value="<?= isset($_GET['filter']) ? $_GET['filter'] : '' ?>" required>

        <input type="submit" value="Generate Report">
    </form>

    <h3>Generated Reports</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Location</th>
                <th>Researcher</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $report): ?>
            <tr>
                <td><?= $report['date']; ?></td>
                <td><?= $report['location']; ?></td>
                <td><?= $report['researcher']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
