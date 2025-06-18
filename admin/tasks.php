<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

try {
    $db = getDB();

    // Fetch all tasks with user info
    $stmt = $db->query("SELECT t.id, t.title, t.deadline, t.priority, t.created_at, u.username, u.full_name 
                        FROM tasks t 
                        JOIN users u ON t.user_id = u.id 
                        ORDER BY t.created_at DESC");
    $tasks = $stmt->fetchAll();

} catch (Exception $e) {
    $error = "Terjadi kesalahan saat memuat data tugas.";
}
?>

<?php include '../includes/header.php'; ?>

<div class="container" style="padding: 2rem 1rem; max-width: 960px; margin: 0 auto;">
    <h1 style="margin-bottom: 1.5rem; font-weight: 700; color: #4a4a4a;">📋 Daftar Semua Tugas</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom: 1rem;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead style="background: #764ba2; color: white;">
            <tr>
                <th style="padding: 0.75rem; text-align: left;">ID</th>
                <th style="padding: 0.75rem; text-align: left;">Judul Tugas</th>
                <th style="padding: 0.75rem; text-align: left;">Pengguna</th>
                <th style="padding: 0.75rem; text-align: left;">Deadline</th>
                <th style="padding: 0.75rem; text-align: left;">Prioritas</th>
                <th style="padding: 0.75rem; text-align: left;">Dibuat Pada</th>
                <th style="padding: 0.75rem; text-align: left;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
            <tr style="border-bottom: 1px solid #ddd; transition: background-color 0.3s;">
                <td style="padding: 0.75rem;"><?= $task['id'] ?></td>
                <td style="padding: 0.75rem;"><?= htmlspecialchars($task['title']) ?></td>
                <td style="padding: 0.75rem;"><?= htmlspecialchars($task['full_name']) ?> (<?= htmlspecialchars($task['username']) ?>)</td>
                <td style="padding: 0.75rem;"><?= date('d M Y H:i', strtotime($task['deadline'])) ?></td>
                <td style="padding: 0.75rem;"><?= htmlspecialchars($task['priority']) ?></td>
                <td style="padding: 0.75rem;"><?= date('d M Y', strtotime($task['created_at'])) ?></td>
                <td style="padding: 0.75rem;">
                    <a href="../edit_task.php?id=<?= $task['id'] ?>" class="btn btn-warning btn-sm" style="background-color: #ffc107; border: none; color: #212529; padding: 0.375rem 0.75rem; border-radius: 6px; font-weight: 600; text-decoration: none; transition: background-color 0.3s;">Edit</a>
                    <!-- Optionally add delete functionality -->
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
