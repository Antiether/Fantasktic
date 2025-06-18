<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

try {
    $db = getDB();

    // Total users
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $total_users = $stmt->fetch()['total'];

    // Total tasks
    $stmt = $db->query("SELECT COUNT(*) as total FROM tasks");
    $total_tasks = $stmt->fetch()['total'];

    // Completed tasks
    $stmt = $db->query("SELECT COUNT(DISTINCT t.id) as total FROM tasks t 
                       LEFT JOIN subtasks st ON t.id = st.task_id 
                       WHERE t.id NOT IN (SELECT DISTINCT task_id FROM subtasks WHERE is_done = 0)
                       AND t.id IN (SELECT DISTINCT task_id FROM subtasks)");
    $completed_tasks = $stmt->fetch()['total'];

    // Tasks by priority
    $stmt = $db->query("SELECT priority, COUNT(*) as count FROM tasks GROUP BY priority");
    $tasks_by_priority = $stmt->fetchAll();

    // Active users (users with tasks)
    $stmt = $db->query("SELECT COUNT(DISTINCT user_id) as total FROM tasks");
    $active_users = $stmt->fetch()['total'];

} catch (Exception $e) {
    $error = "Terjadi kesalahan saat memuat data laporan.";
}
?>

<?php include '../includes/header.php'; ?>

<style>
    .container {
        max-width: 800px;
        margin: 2rem auto;
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 6px 30px rgba(0,0,0,0.2);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    h1, h2 {
        color: #222;
        font-weight: 700;
        margin-bottom: 1rem;
        text-align: center;
    }
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-weight: 600;
        text-align: center;
    }
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }
    ul {
        list-style: none;
        padding: 0;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-around;
        font-weight: 600;
        font-size: 1.1rem;
        color: #343a40;
    }
    ul li {
        background: #e9ecef;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        flex: 1;
        margin: 0 0.5rem;
        text-align: center;
    }
    table.table {
        width: 100%;
        border-collapse: collapse;
        box-shadow: 0 6px 30px rgba(0,0,0,0.2);
        border-radius: 12px;
        overflow: hidden;
    }
    table.table thead {
        background-color: #0056b3;
        color: white;
        font-weight: 700;
    }
    table.table th, table.table td {
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
        text-align: center;
    }
    table.table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
</style>

<div class="container">
    <h1>📊 Laporan Sistem</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <ul>
        <li>Total Pengguna: <?= $total_users ?></li>
        <li>Total Tugas: <?= $total_tasks ?></li>
        <li>Tugas Selesai: <?= $completed_tasks ?></li>
        <li>Pengguna Aktif: <?= $active_users ?></li>
    </ul>

    <h2>Tugas Berdasarkan Prioritas</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Prioritas</th>
                <th>Jumlah Tugas</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks_by_priority as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['priority']) ?></td>
                <td><?= $row['count'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
