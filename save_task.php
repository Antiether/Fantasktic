<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'];
  $deadline = $_POST['deadline']; // format: 2025-06-06T14:00
  $priority = $_POST['priority'];
  $subtasks_input = explode("\n", trim($_POST['subtasks']));

  $subtasks = [];
  foreach ($subtasks_input as $line) {
    $clean = trim($line);
    if ($clean !== '') {
      $subtasks[] = ['text' => $clean, 'done' => false];
    }
  }

  $new_task = [
    'title' => $title,
    'deadline' => date('Y-m-d H:i', strtotime($deadline)),
    'priority' => $priority,
    'subtasks' => $subtasks
  ];

  $file = 'data/tasks.json';
  $tasks = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

  if (isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $new_task['subtasks'] = [];

    foreach ($tasks[$id]['subtasks'] as $i => $old_sub) {
      $text = $subtasks[$i]['text'] ?? '';
      $done = $old_sub['done'] ?? false;
      $new_task['subtasks'][] = ['text' => $text, 'done' => $done];
    }

    $tasks[$id] = $new_task;
  } else {
    $tasks[] = $new_task;
  }

  file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT));
}

header('Location: index.php');
exit;