<?php
include('database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $challenge_name = $_POST['challenge_name'];
    addChallenge($challenge_name);
    header('Location: index1.html');
    exit();
}
?>
