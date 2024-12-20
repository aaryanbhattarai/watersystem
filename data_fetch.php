<?php
include 'database.php';

// Fetch reservoir data
$query = "SELECT name, level, capacity FROM reservoirs ORDER BY name";
$result = mysqli_query($conn, $query);

$reservoirData = [];

while ($row = mysqli_fetch_assoc($result)) {
    $reservoirData[] = $row;
}

// Return JSON data
echo json_encode($reservoirData);
?>
