<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check'])) {
  $file = 'data/tasks.json';
  if (!file_exists($file)) {
    header('Location: index.php');
    exit;
  }

  $tasks = json_decode(file_get_contents($file), true);

  foreach ($_POST['check'] as $taskIndex => $subtasks) {
    foreach ($tasks[$taskIndex]['subtasks'] as $i => &$subtask) {
      $subtask['done'] = isset($subtasks[$i]);
    }
  }

  file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT));
}

header('Location: index.php');
exit;
