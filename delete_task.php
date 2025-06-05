<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $file = 'data/tasks.json';
  if (file_exists($file)) {
    $tasks = json_decode(file_get_contents($file), true);
    $id = (int)$_POST['id'];

    if (isset($tasks[$id])) {
      array_splice($tasks, $id, 1); // hapus elemen dari array
      file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT));
    }
  }
}

header('Location: index.php');
exit;
