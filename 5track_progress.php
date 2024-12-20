<?php
include('database.php');

if (isset($_GET['id'])) {
    $challenge_id = $_GET['id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $task_id = $_POST['task_id'];
        $status = $_POST['status'];
        updateTaskStatus($task_id, $status);
        header('Location: index.html');
        exit();
    }

    $challenge = getChallengeById($challenge_id);
    $tasks = getTasksByChallengeId($challenge_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Progress</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Track Progress for Challenge: <?php echo $challenge['name']; ?></h1>

        <form action="" method="POST">
            <label for="task">Select Task:</label>
            <select name="task_id" required>
                <?php foreach ($tasks as $task) {
                    echo "<option value='{$task['id']}'>{$task['name']}</option>";
                } ?>
            </select>

            <label for="status">Progress Status:</label>
            <select name="status" required>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
                <option value="Pending">Pending</option>
            </select>

            <button type="submit">Update Status</button>
        </form>
    </div>
</body>
</html>
