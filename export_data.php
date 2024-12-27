<?php
include 'db.php';

// Fetch all data to export
$sql = "SELECT * FROM reservoir_data ORDER BY date_recorded DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Set the headers to download a CSV file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="reservoir_data.csv"');

    $output = fopen('php://output', 'w');
    
    // Add CSV column headers
    fputcsv($output, ['Date Recorded', 'Water Level', 'Temperature', 'pH Level', 'Turbidity']);
    
    // Output each row of the data
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    
    fclose($output);
}

$conn->close();
exit;
