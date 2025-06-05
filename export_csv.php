<?php
$filename = "tasks_export_" . date("Ymd_His") . ".csv";
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=$filename");

$tasks = json_decode(file_get_contents("data/tasks.json"), true);
$output = fopen("php://output", "w");

// Header CSV
fputcsv($output, ["Judul", "Deadline", "Prioritas", "Subtugas"]);

foreach ($tasks as $task) {
  $subs = implode(" | ", array_map(function($s) {
    return ($s['done'] ? "[✓] " : "[ ] ") . $s['text'];
  }, $task['subtasks']));

  fputcsv($output, [$task['title'], $task['deadline'], $task['priority'], $subs]);
}

fclose($output);
exit;
