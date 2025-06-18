<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $deadline = $_POST['deadline'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $subtasks_input = explode("\n", trim($_POST['subtasks'] ?? ''));
    $user_id = getCurrentUserId();
    
    if (empty($title) || empty($deadline) || empty($priority)) {
        header('Location: add_task.php?error=missing_fields');
        exit;
    }
    
    try {
        $db = getDB();
        $db->beginTransaction();
        
        if (isset($_POST['edit_id'])) {
            // Update existing task
            $task_id = (int)$_POST['edit_id'];
            
            // Verify task belongs to current user
            $verify_stmt = $db->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
            $verify_stmt->execute([$task_id, $user_id]);
            
            if (!$verify_stmt->fetch()) {
                throw new Exception("Unauthorized access");
            }
            
            // Update task
            $stmt = $db->prepare("UPDATE tasks SET title = ?, deadline = ?, priority = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $deadline, $priority, $task_id, $user_id]);
            
            // Delete existing subtasks
            $delete_stmt = $db->prepare("DELETE FROM subtasks WHERE task_id = ?");
            $delete_stmt->execute([$task_id]);
            
        } else {
            // Insert new task
            $stmt = $db->prepare("INSERT INTO tasks (user_id, title, deadline, priority) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $deadline, $priority]);
            $task_id = $db->lastInsertId();
        }
        
        // Insert subtasks
        if (!empty($subtasks_input[0])) {
            $subtask_stmt = $db->prepare("INSERT INTO subtasks (task_id, text, sort_order) VALUES (?, ?, ?)");
            
            foreach ($subtasks_input as $index => $subtask_text) {
                $clean_text = trim($subtask_text);
                if (!empty($clean_text)) {
                    $subtask_stmt->execute([$task_id, $clean_text, $index + 1]);
                }
            }
        }
        
        $db->commit();
        header('Location: index.php?success=task_saved');
        
    } catch (Exception $e) {
        $db->rollBack();
        header('Location: add_task.php?error=save_failed');
    }
    
    exit;
}

header('Location: index.php');
exit;
?>
