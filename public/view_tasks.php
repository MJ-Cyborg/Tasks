<?php
include 'db.php';

$stmt = $pdo->query("SELECT * FROM tasks");
$tasks = $stmt->fetchAll();

foreach ($tasks as $task) {
    echo "<div>";
    echo "<h3>" . htmlspecialchars($task['task_name']) . "</h3>";
    echo "<p>" . htmlspecialchars($task['description']) . "</p>";
    echo "<p>Points: " . htmlspecialchars($task['points']) . "</p>";
    echo "<a href='complete_task.php?task_id=" . $task['id'] . "'>Complete Task</a>";
    echo "</div>";
}
?>
