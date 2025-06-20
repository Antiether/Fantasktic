<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = (int)($_POST['id'] ?? 0);

    if ($task_id <= 0) {
        header('Location: tasks.php?error=invalid_id');
        exit;
    }

    try {
        $db = getDB();

        // Delete the task
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);

        header('Location: tasks.php?success=task_deleted');
        exit;
    } catch (Exception $e) {
        header('Location: tasks.php?error=delete_failed');
        exit;
    }
} else {
    header('Location: tasks.php');
    exit;
}
?>
