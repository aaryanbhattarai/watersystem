<?php
include 'database.php';

try {
    // Fetch uploaded documents
    $query = "SELECT * FROM documents ORDER BY uploaded_at DESC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<li><a href='" . htmlspecialchars($row['file_path']) . "' target='_blank'>" . htmlspecialchars($row['title']) . "</a> (" . htmlspecialchars($row['category']) . ")</li>";
        }
    } else {
        echo "<li>No documents have been uploaded yet.</li>";
    }
} catch (Exception $e) {
    echo "<li>Error fetching documents: " . htmlspecialchars($e->getMessage()) . "</li>";
}

$conn->close();
?>
