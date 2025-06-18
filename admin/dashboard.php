<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

try {
    $db = getDB();
    
    // Get statistics
    $stats = [];
    
    // Total users
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $stats['total_users'] = $stmt->fetch()['total'];
    
    // Total tasks
    $stmt = $db->query("SELECT COUNT(*) as total FROM tasks");
    $stats['total_tasks'] = $stmt->fetch()['total'];
    
    // Completed tasks
    $stmt = $db->query("SELECT COUNT(DISTINCT t.id) as total FROM tasks t 
                       LEFT JOIN subtasks st ON t.id = st.task_id 
                       WHERE t.id NOT IN (SELECT DISTINCT task_id FROM subtasks WHERE is_done = 0)
                       AND t.id IN (SELECT DISTINCT task_id FROM subtasks)");
    $stats['completed_tasks'] = $stmt->fetch()['total'];
    
    // Active users (users with tasks)
    $stmt = $db->query("SELECT COUNT(DISTINCT user_id) as total FROM tasks");
    $stats['active_users'] = $stmt->fetch()['total'];
    
    // Get recent users
    $stmt = $db->prepare("SELECT id, username, full_name, email, created_at, is_active FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $recent_users = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = "Terjadi kesalahan saat memuat data.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fantasktic</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fa;
            margin: 0;
            padding: 0;
            color: #34495e;
        }

        .admin-header {
            background: linear-gradient(135deg, #4a90e2 0%, #357ABD 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 6px 20px rgba(53, 122, 189, 0.5);
            text-align: center;
        }

        .admin-header h1 {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 6px rgba(0,0,0,0.4);
        }

        .admin-header p {
            font-size: 1.25rem;
            font-weight: 600;
            color: #cce5ff;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 25px rgba(53, 122, 189, 0.15);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: default;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 40px rgba(53, 122, 189, 0.3);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 0.75rem;
        }

        .stat-number {
            font-size: 2.25rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 600;
            font-size: 1rem;
        }

        .admin-nav {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 25px rgba(53, 122, 189, 0.15);
            margin-bottom: 2rem;
        }

        .admin-nav-links {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .admin-nav-links a {
            flex: 1 1 150px;
            text-align: center;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            text-decoration: none;
            transition: background 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 6px 15px rgba(53, 122, 189, 0.6);
            user-select: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .admin-nav-links a:hover {
            box-shadow: 0 8px 25px rgba(53, 122, 189, 0.8);
        }

        .btn-primary {
            background: #357ABD;
        }

        .btn-secondary {
            background: #4a90e2;
        }

        .btn-warning {
            background: #f5a623;
            color: #2c3e50;
            box-shadow: 0 6px 15px rgba(245, 166, 35, 0.6);
        }

        .btn-warning:hover {
            background: #d48806;
            box-shadow: 0 8px 25px rgba(212, 136, 6, 0.8);
        }

        .btn-success {
            background: #7ed321;
            color: #2c3e50;
            box-shadow: 0 6px 15px rgba(126, 211, 33, 0.6);
        }

        .btn-success:hover {
            background: #6ab91a;
            box-shadow: 0 8px 25px rgba(106, 185, 26, 0.8);
        }

        .users-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 25px rgba(53, 122, 189, 0.15);
        }

        .table-header {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #dee2e6;
        }

        .table-header h3 {
            margin: 0;
            font-weight: 700;
            color: #2c3e50;
        }

        .table-content {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h2>⚙️ Admin Panel</h2>
            </div>
            <div class="nav-menu" style="padding-right: 2rem;">
                <div class="nav-user">
                    <span class="user-greeting" style="margin-right: 1rem;">
                        Admin: <strong><?= htmlspecialchars(getCurrentUser()['full_name']) ?></strong>
                    </span>
                    <div class="nav-actions">
                        <a href="../index.php" class="nav-link">📋 Tasks</a>
                        <a href="../logout.php" class="nav-link logout-link">🚪 Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        .navbar {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            padding: 1rem 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .nav-brand h2 {
            font-weight: 700;
            font-size: 1.75rem;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .nav-brand h2::before {
            content: "⚙️";
            font-size: 1.8rem;
        }

        .nav-menu {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            font-size: 1rem;
            color: #34495e;
        }

        .user-greeting {
            font-weight: 600;
        }

        .nav-actions {
            display: flex;
            gap: 1rem;
        }

        .nav-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #4a63d2;
            text-decoration: underline;
        }

        .logout-link {
            color: #dc3545;
        }

        .logout-link:hover {
            color: #b02a37;
        }

        @media (max-width: 600px) {
            .nav-menu {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>

    <div class="admin-header">
        <div class="container">
            <h1>📊 Dashboard Admin</h1>
            <p>Kelola pengguna dan monitor aktivitas sistem</p>
        </div>
    </div>

    <div class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Total Pengguna</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-number"><?= $stats['total_tasks'] ?></div>
                <div class="stat-label">Total Tugas</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?= $stats['completed_tasks'] ?></div>
                <div class="stat-label">Tugas Selesai</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-number"><?= $stats['active_users'] ?></div>
                <div class="stat-label">Pengguna Aktif</div>
            </div>
        </div>

        <!-- Admin Navigation -->
        <div class="admin-nav">
            <div class="admin-nav-links">
                <a href="users.php" class="btn btn-primary">👥 Kelola Pengguna</a>
                <a href="tasks.php" class="btn btn-secondary">📋 Lihat Semua Tugas</a>
                <a href="reports.php" class="btn btn-warning">📊 Laporan</a>
                <a href="settings.php" class="btn btn-success">⚙️ Pengaturan</a>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="users-table">
            <div class="table-header">
                <h3>👥 Pengguna Terbaru</h3>
            </div>
            <div class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="status-badge <?= $user['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                    <?= $user['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-warning" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
