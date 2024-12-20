<?php
include('database.php');

if (isset($_GET['id'])) {
    $challenge_id = $_GET['id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $task_name = $_POST['task_name'];
        $assigned_to = $_POST['assigned_to'];
        assignTask($challenge_id, $task_name, $assigned_to);
        header('Location: index.html');
        exit();
    }

    $challenge = getChallengeById($challenge_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Task</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Assign Task to Challenge: <?php echo $challenge['name']; ?></h1>

        <form action="" method="POST">
            <input type="text" name="task_name" placeholder="Enter task name" required>
            <input type="text" name="assigned_to" placeholder="Assign to" required>
            <button type="submit">Assign Task</button>
        </form>
    </div>
</body>
</html>
