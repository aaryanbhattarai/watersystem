<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Documents</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Search Research Documents</h1>
        <form method="GET" action="">
            <label for="query">Search by Title or Category:</label>
            <input type="text" id="query" name="query">
            <input type="submit" value="Search">
        </form>

        <h2>Results:</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Uploaded On</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($_GET['query'])) {
                    $query = mysqli_real_escape_string($conn, $_GET['query']);
                    $sql = "SELECT * FROM research_documents WHERE title LIKE '%$query%' OR category LIKE '%$query%' ORDER BY upload_date DESC";
                } else {
                    $sql = "SELECT * FROM research_documents ORDER BY upload_date DESC";
                }
                $result = mysqli_query($conn, $sql);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['title']}</td>
                            <td>{$row['category']}</td>
                            <td>{$row['upload_date']}</td>
                            <td><a href='{$row['file_path']}' target='_blank'>View</a></td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
