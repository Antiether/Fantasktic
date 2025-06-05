<?php include 'includes/header.php'; ?>

<?php
$tasks = file_exists('data/tasks.json') ? json_decode(file_get_contents('data/tasks.json'), true) : [];
$date_today = date('Y-m-d');
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

// Filter by search keyword
if ($search) {
  $tasks = array_filter($tasks, function ($task) use ($search) {
    return stripos($task['title'], $search) !== false;
  });
}

// Filter by deadline (today or this week)
if ($filter === 'today') {
  $tasks = array_filter($tasks, function ($task) {
    return date('Y-m-d', strtotime($task['deadline'])) === date('Y-m-d');
  });
} elseif ($filter === 'week') {
  $now = strtotime('today');
  $end = strtotime('+6 days', $now);

  $tasks = array_filter($tasks, function ($task) use ($now, $end) {
    $dl = strtotime($task['deadline']);
    return $dl >= $now && $dl <= $end;
  });
}
?>

<div class="header">
    <div class="container">
        <h1>📋 Task Manager</h1>
        <p>Kelola tugas Anda dengan mudah dan efisien</p>
    </div>
</div>

<div class="container">
    <!-- Search and Filter Section -->
    <div class="search-section">
        <form method="get" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="🔍 Cari tugas..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            
            <select name="filter" class="filter-select">
                <option value="">📅 Semua Deadline</option>
                <option value="today" <?= ($_GET['filter'] ?? '') == 'today' ? 'selected' : '' ?>>⚡ Hari Ini</option>
                <option value="week" <?= ($_GET['filter'] ?? '') == 'week' ? 'selected' : '' ?>>📆 Minggu Ini</option>
            </select>

            <button type="submit" class="btn btn-primary">Terapkan</button>
            <a href="index.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <!-- Action Section -->
    <div class="action-section">
        <h2 class="section-title">Daftar Tugas</h2>
        <div>
            <a href="add_task.php" class="btn btn-success">➕ Tambah Tugas</a>
            <a href="export_csv.php" target="_blank" class="btn btn-warning">📊 Export CSV</a>
        </div>
    </div>

    <!-- Tasks Display -->
    <?php if (count($tasks) === 0): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📝</div>
            <h3><?= $search ? 'Tidak ada tugas yang sesuai dengan pencarian' : 'Belum ada tugas' ?></h3>
            <p><?= $search ? 'Coba dengan kata kunci lain' : 'Mulai dengan menambahkan tugas pertama Anda' ?></p>
            <?php if (!$search): ?>
                <a href="add_task.php" class="btn btn-primary" style="margin-top: 1rem;">➕ Tambah Tugas Pertama</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="task-grid">
            <?php foreach ($tasks as $i => $task): 
                $deadline = $task['deadline'];
                $prioritas = strtolower($task['priority']);
                $date_diff = (strtotime($deadline) - strtotime($date_today)) / 86400;

                $reminder_class = '';
                if ($date_diff <= 1) $reminder_class = 'reminder-soon';
                
                // Format deadline yang lebih readable
                $deadline_formatted = date('d M Y', strtotime($deadline));
                $deadline_day = date('l', strtotime($deadline));
                
                // Hitung progress subtasks
                $total_subtasks = count($task['subtasks']);
                $completed_subtasks = array_reduce($task['subtasks'], function($carry, $subtask) {
                    return $carry + ($subtask['done'] ? 1 : 0);
                }, 0);
                $progress_percentage = $total_subtasks > 0 ? ($completed_subtasks / $total_subtasks) * 100 : 0;
            ?>
            
            <div class="task priority-<?= $prioritas ?> <?= $reminder_class ?>">
                <div class="task-title"><?= htmlspecialchars($task['title']) ?></div>
                
                <div class="task-meta">
                    <div class="task-deadline">
                        📅 <?= $deadline_formatted ?>
                        <?php if ($date_diff <= 1): ?>
                            <?php if ($date_diff < 0): ?>
                                <span style="color: #dc3545; font-weight: bold;">(Terlambat)</span>
                            <?php elseif ($date_diff == 0): ?>
                                <span style="color: #dc3545; font-weight: bold;">(Hari ini!)</span>
                            <?php else: ?>
                                <span style="color: #ffc107; font-weight: bold;">(Besok)</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="task-priority"><?= ucfirst($task['priority']) ?></div>
                </div>

                <?php if ($total_subtasks > 0): ?>
                    <div style="margin-bottom: 1rem;">
                        <small style="color: #6c757d;">Progress: <?= $completed_subtasks ?>/<?= $total_subtasks ?> selesai (<?= round($progress_percentage) ?>%)</small>
                        <div style="background: #e9ecef; height: 8px; border-radius: 4px; margin-top: 0.5rem;">
                            <div style="background: <?= $progress_percentage == 100 ? '#28a745' : '#667eea' ?>; height: 100%; width: <?= $progress_percentage ?>%; border-radius: 4px; transition: width 0.3s ease;"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="update_task.php" method="post">
                    <div class="subtasks">
                        <?php foreach ($task['subtasks'] as $j => $sub): ?>
                            <div class="subtask-item">
                                <input class="subtask-checkbox" type="checkbox" name="check[<?= $i ?>][<?= $j ?>]" <?= $sub['done'] ? 'checked' : '' ?>>
                                <span class="subtask-text" style="<?= $sub['done'] ? 'text-decoration: line-through; color: #6c757d;' : '' ?>">
                                    <?= htmlspecialchars($sub['text']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="task-actions">
                        <button type="submit" class="btn btn-primary">✅ Update</button>
                    </div>
                </form>
                
                <div class="task-actions" style="margin-top: 0.5rem;">
                    <form action="edit_task.php" method="get" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $i ?>">
                        <button type="submit" class="btn btn-warning">✏️ Edit</button>
                    </form>
                    
                    <form action="delete_task.php" method="post" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $i ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus tugas ini?')">🗑️ Hapus</button>
                    </form>
                </div>
            </div>
            
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>