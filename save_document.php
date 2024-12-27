<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $file = $_FILES['file'];

    // File upload path
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file["name"]);

    // Move uploaded file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Save file details to database
        $sql = "INSERT INTO research_documents (title, category, file_path) VALUES ('$title', '$category', '$target_file')";
        if (mysqli_query($conn, $sql)) {
            echo "Document uploaded successfully!";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Failed to upload file.";
    }
}
?>
