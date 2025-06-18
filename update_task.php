<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = (int)($_POST['task_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $deadline = $_POST['deadline'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $user_id = getCurrentUserId();

    // Get subtasks done status array from form
    $subtasks_done = $_POST['subtasks_done'] ?? [];

    if ($task_id <= 0 || empty($title) || empty($deadline) || empty($priority)) {
        header('Location: edit_task.php?id=' . $task_id . '&error=missing_fields');
        exit;
    }

    try {
        $db = getDB();
        $db->beginTransaction();

        // Verify task belongs to current user
        $verify_stmt = $db->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
        $verify_stmt->execute([$task_id, $user_id]);

        if (!$verify_stmt->fetch()) {
            throw new Exception("Unauthorized access");
        }

        // Update task
        $stmt = $db->prepare("UPDATE tasks SET title = ?, deadline = ?, priority = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $deadline, $priority, $task_id, $user_id]);

        // Update subtasks is_done status
        $subtask_update_stmt = $db->prepare("UPDATE subtasks SET is_done = ? WHERE id = ? AND task_id = ?");

        // Fetch all subtasks for the task
        $subtask_fetch_stmt = $db->prepare("SELECT id FROM subtasks WHERE task_id = ?");
        $subtask_fetch_stmt->execute([$task_id]);
        $existing_subtasks = $subtask_fetch_stmt->fetchAll();

        foreach ($existing_subtasks as $subtask) {
            $is_done = isset($subtasks_done[$subtask['id']]) ? 1 : 0;
            $subtask_update_stmt->execute([$is_done, $subtask['id'], $task_id]);
        }

        $db->commit();
        header('Location: index.php?success=task_updated');

    } catch (Exception $e) {
        $db->rollBack();
        header('Location: edit_task.php?id=' . $task_id . '&error=update_failed');
    }

    exit;
}

header('Location: index.php');
exit;
?>
