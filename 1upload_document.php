<?php


include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $docTitle = $_POST['doc-title'];
    $category = $_POST['category'];

    // File upload handling
    if (isset($_FILES['doc-file']) && $_FILES['doc-file']['error'] == 0) {
        $fileName = $_FILES['doc-file']['name'];
        $fileTmpName = $_FILES['doc-file']['tmp_name'];
        $uploadDir = 'uploads/';
        $filePath = $uploadDir . basename($fileName);

        // Move uploaded file to the target directory
        if (move_uploaded_file($fileTmpName, $filePath)) {
            // Insert file information into the database
            $stmt = $conn->prepare("INSERT INTO documents (title, category, file_path, uploaded_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $docTitle, $category, $filePath);

            if ($stmt->execute()) {
                echo "File uploaded successfully.";
            } else {
                echo "Error inserting data into the database.";
            }
            $stmt->close();
        } else {
            echo "Error uploading the file.";
        }
    } else {
        echo "File upload error: " . $_FILES['doc-file']['error'];
    }
}

$conn->close();
?>

?>
