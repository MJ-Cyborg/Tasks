<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "You need to log in to complete tasks.";
    exit;
}

if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];
    $user_id = $_SESSION['user_id'];

    // Insert into user_tasks
    $stmt = $pdo->prepare("INSERT INTO user_tasks (user_id, task_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $task_id]);

    // Update user points
    $stmt = $pdo->prepare("UPDATE users SET points = points + (SELECT points FROM tasks WHERE id = ?) WHERE id = ?");
    $stmt->execute([$task_id, $user_id]);

    echo "Task completed successfully! You earned points.";
} else {
    echo "Task ID is missing.";
}
?>
