<?php


// Fetch all challenges
function getChallenges() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM challenges");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add new challenge
function addChallenge($name) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO challenges (name, status) VALUES (?, ?)");
    $stmt->execute([$name, 'Pending']);
}

// Get challenge by ID
function getChallengeById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM challenges WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Add task to a challenge
function assignTask($challenge_id, $task_name, $assigned_to) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO tasks (challenge_id, name, assigned_to, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$challenge_id, $task_name, $assigned_to, 'Pending']);
}

// Fetch tasks by challenge ID
function getTasksByChallengeId($challenge_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE challenge_id = ?");
    $stmt->execute([$challenge_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Update task status
function updateTaskStatus($task_id, $status) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    $stmt->execute([$status, $task_id]);
}
?>
