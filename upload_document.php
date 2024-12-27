<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Research Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Upload Research Document</h1>
        <form action="save_document.php" method="POST" enctype="multipart/form-data">
            <label for="title">Document Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="Water Quality">Water Quality</option>
                <option value="Technical Reports">Technical Reports</option>
                <option value="Case Studies">Case Studies</option>
                <option value="Policies">Policies</option>
            </select>

            <label for="file">Select File:</label>
            <input type="file" id="file" name="file" required>

            <input type="submit" value="Upload">
        </form>
    </div>
</body>
</html>
