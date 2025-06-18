<?php 
require_once 'config/database.php';
include 'includes/header.php'; 

$current_user_id = getCurrentUserId();
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

try {
    $db = getDB();
    
    // Base query
    $sql = "SELECT t.*, 
                   COUNT(st.id) as total_subtasks,
                   COUNT(CASE WHEN st.is_done = 1 THEN 1 END) as completed_subtasks
            FROM tasks t 
            LEFT JOIN subtasks st ON t.id = st.task_id 
            WHERE t.user_id = ?";
    
    $params = [$current_user_id];
    
    // Add search filter
    if ($search) {
        $sql .= " AND t.title LIKE ?";
        $params[] = "%$search%";
    }
    
    $sql .= " GROUP BY t.id";
    
    // Add deadline filter
    if ($filter === 'today') {
        $sql .= " HAVING DATE(t.deadline) = CURDATE()";
    } elseif ($filter === 'week') {
        $sql .= " HAVING t.deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)";
    }
    
    $sql .= " ORDER BY t.deadline ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
    
} catch (Exception $e) {
    $tasks = [];
    $error = "Terjadi kesalahan saat memuat data.";
}

$date_today = date('Y-m-d');
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

    body {
        font-family: 'Poppins', sans-serif;
        background: #f4f7fa;
        margin: 0;
        padding: 0;
        color: #34495e;
    }

    .header {
        background: #667eea;
        color: white;
        padding: 2rem 0;
        text-align: center;
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    }

    .header h1 {
        margin: 0;
        font-weight: 600;
        font-size: 2.5rem;
    }

    .header p {
        margin: 0.5rem 0 0;
        font-size: 1.2rem;
        font-weight: 400;
    }

    .container {
        max-width: 960px;
        margin: 2rem auto 4rem;
        padding: 0 1rem;
    }

    .search-section {
        display: flex;
        justify-content: center;
        margin-bottom: 1.5rem;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .search-form {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        width: 100%;
        max-width: 600px;
    }

    .search-input, .filter-select {
        padding: 0.5rem 1rem;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        outline: none;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        flex-grow: 1;
        min-width: 150px;
    }

    .search-input:focus, .filter-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 8px rgba(102, 126, 234, 0.6);
    }

    .btn {
        cursor: pointer;
        border: none;
        border-radius: 8px;
        padding: 0.6rem 1.2rem;
        font-weight: 600;
        font-size: 1rem;
        transition: background 0.3s ease, box-shadow 0.3s ease;
        user-select: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        text-decoration: none;
        color: white;
    }

    .btn-primary {
        background: #667eea;
        box-shadow: 0 6px 15px rgba(102, 126, 234, 0.6);
    }

    .btn-primary:hover {
        background: #5a6edb;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.8);
    }

    .btn-secondary {
        background: #95a5a6;
        box-shadow: 0 6px 15px rgba(149, 165, 166, 0.6);
    }

    .btn-secondary:hover {
        background: #7f8c8d;
        box-shadow: 0 8px 20px rgba(149, 165, 166, 0.8);
    }

    .btn-success {
        background: #28a745;
        box-shadow: 0 6px 15px rgba(40, 167, 69, 0.6);
    }

    .btn-success:hover {
        background: #218838;
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.8);
    }

    .btn-warning {
        background: #ffc107;
        color: #34495e;
        box-shadow: 0 6px 15px rgba(255, 193, 7, 0.6);
    }

    .btn-warning:hover {
        background: #e0a800;
        box-shadow: 0 8px 20px rgba(255, 193, 7, 0.8);
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #7f8c8d;
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    .section-title {
        font-weight: 600;
        font-size: 1.75rem;
        margin-bottom: 1rem;
        color: #2c3e50;
    }

    .action-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .task-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }

    .task {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 1.5rem 1.5rem 2rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: box-shadow 0.3s ease;
    }

    .task:hover {
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .task-title {
        font-weight: 600;
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
        color: #34495e;
    }

    .task-meta {
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
        color: #7f8c8d;
        margin-bottom: 1rem;
    }

    .task-deadline {
        font-weight: 600;
    }

    .task-priority {
        font-weight: 600;
        text-transform: capitalize;
    }

    .priority-rendah {
        border-left: 6px solid #28a745;
    }

    .priority-sedang {
        border-left: 6px solid #ffc107;
    }

    .priority-tinggi {
        border-left: 6px solid #dc3545;
    }

    .reminder-soon .task-deadline {
        color: #dc3545;
        font-weight: 700;
    }

    .subtasks {
        margin-bottom: 1rem;
    }

    .subtask-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.3rem;
    }

    .subtask-checkbox {
        margin-right: 0.5rem;
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .subtask-text {
        font-size: 1rem;
        color: #34495e;
    }

    .task-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .task-actions form {
        margin: 0;
    }

    .task-actions button, .task-actions a.btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
        border-radius: 8px;
        cursor: pointer;
        border: none;
        transition: background 0.3s ease, box-shadow 0.3s ease;
        user-select: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        text-decoration: none;
    }

    .btn-danger {
        background: #dc3545;
        box-shadow: 0 6px 15px rgba(220, 53, 69, 0.6);
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
        box-shadow: 0 8px 20px rgba(220, 53, 69, 0.8);
    }

    .btn-warning {
        background: #ffc107;
        color: #34495e;
        box-shadow: 0 6px 15px rgba(255, 193, 7, 0.6);
    }

    .btn-warning:hover {
        background: #e0a800;
        box-shadow: 0 8px 20px rgba(255, 193, 7, 0.8);
    }

    .btn-primary {
        background: #667eea;
        color: white;
        box-shadow: 0 6px 15px rgba(102, 126, 234, 0.6);
    }

    .btn-primary:hover {
        background: #5a6edb;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.8);
    }
    </style>
    <style>
        .btn-group {
            display: inline-flex;
            gap: 0.5rem;
        }
    </style>

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
            <input type="text" name="search" class="search-input" placeholder="🔍 Cari tugas..." value="<?= htmlspecialchars($search) ?>">
            
            <select name="filter" class="filter-select">
                <option value="">📅 Semua Deadline</option>
                <option value="today" <?= $filter == 'today' ? 'selected' : '' ?>>⚡ Hari Ini</option>
                <option value="week" <?= $filter == 'week' ? 'selected' : '' ?>>📆 Minggu Ini</option>
            </select>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="index.php" class="btn btn-secondary">Reset</a>
            </div>
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
            <?php foreach ($tasks as $task): 
                $deadline = $task['deadline'];
                $prioritas = strtolower($task['priority']);
                $date_diff = (strtotime($deadline) - strtotime($date_today)) / 86400;

                $reminder_class = '';
                if ($date_diff <= 1) $reminder_class = 'reminder-soon';
                
                $deadline_formatted = date('d M Y H:i', strtotime($deadline));
                
                $total_subtasks = $task['total_subtasks'];
                $completed_subtasks = $task['completed_subtasks'];
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
                    
                    <!-- Display subtasks -->
                    <?php
                    $subtask_stmt = $db->prepare("SELECT * FROM subtasks WHERE task_id = ? ORDER BY sort_order ASC");
                    $subtask_stmt->execute([$task['id']]);
                    $subtasks = $subtask_stmt->fetchAll();
                    ?>
                    
                    <form action="update_task.php" method="post">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        <div class="subtasks">
                            <?php foreach ($subtasks as $subtask): ?>
                                <div class="subtask-item">
                                    <input class="subtask-checkbox" type="checkbox" name="subtask_ids[]" value="<?= $subtask['id'] ?>" <?= $subtask['is_done'] ? 'checked' : '' ?>>
                                    <span class="subtask-text" style="<?= $subtask['is_done'] ? 'text-decoration: line-through; color: #6c757d;' : '' ?>">
                                        <?= htmlspecialchars($subtask['text']) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="task-actions">
                            <button type="submit" class="btn btn-primary">✅ Update</button>
                        </div>
                    </form>
                <?php endif; ?>
                
                <div class="task-actions" style="margin-top: 0.5rem;">
                    <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-warning">✏️ Edit</a>
                    <form action="delete_task.php" method="post" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus tugas ini?')">🗑️ Hapus</button>
                    </form>
                </div>
            </div>
            
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
