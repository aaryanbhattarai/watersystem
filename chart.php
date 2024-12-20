<?php
// Include the database connection file
include 'database.php';

// Fetch reservoir data from the database
$query = "SELECT name, level, capacity FROM reservoirs ORDER BY name";
$result = mysqli_query($conn, $query);

// Initialize arrays to store reservoir data
$reservoirData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reservoirData[] = $row;
}

// Function to create a simple bar chart using GD
function createChart($data) {
    // Image dimensions
    $width = 800;
    $height = 400;

    // Create the image
    $image = imagecreatetruecolor($width, $height);

    // Define colors
    $backgroundColor = imagecolorallocate($image, 255, 255, 255); // white background
    $barColor = imagecolorallocate($image, 54, 162, 235); // blue bars
    $textColor = imagecolorallocate($image, 0, 0, 0); // black text

    // Fill the background
    imagefill($image, 0, 0, $backgroundColor);

    // Bar width and spacing
    $barWidth = 60;
    $barSpacing = 100;
    $startX = 50;

    // Draw bars and labels
    $x = $startX;
    foreach ($data as $i => $item) {
        $barHeight = ($item['level'] / 100) * ($height - 100); // scale level to fit image height
        imagefilledrectangle($image, $x, $height - 50, $x + $barWidth, $height - 50 - $barHeight, $barColor);
        imagestring($image, 5, $x + 5, $height - 45, $item['name'], $textColor);
        $x += $barSpacing;
    }

    // Output the image to the browser
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

// Call the function to generate the chart
createChart($reservoirData);
?>
