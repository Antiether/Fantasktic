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
    
    // Active users (users with at least one task)
    $stmt = $db->query("SELECT COUNT(DISTINCT user_id) as total FROM tasks");
    $stats['active_users'] = $stmt->fetch()['total'];
    
    // Get recent users
    $stmt = $db->prepare("SELECT id, username, full_name, email, created_at, is_active FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_users = $stmt->fetchAll();
    
} catch (Exception $e) {
    // die($e->getMessage()); // Uncomment for debugging
    $error = "Terjadi kesalahan saat memuat data.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fantasktic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    
    <style>
        /* Menggunakan Font dari Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        /* --- STYLING UTAMA --- */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f9;
            color: #34495e;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* --- HEADER/NAVBAR BARU --- */
        .navbar {
            background: #ffffff;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-brand h2 .fa-shield-halved {
            color: #667eea;
        }
        
        .nav-user {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-greeting {
            font-weight: 500;
            color: #555;
        }

        .nav-actions {
            display: flex;
            gap: 1rem;
        }

        .nav-link {
            color: #555;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link:hover {
            color: #667eea;
        }

        .logout-link:hover {
            color: #e74c3c;
        }

        /* --- KARTU STATISTIK --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            font-size: 1.8rem;
            width: 60px;
            height: 60px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: #fff;
        }
        
        /* Warna Ikon Kartu Statistik */
        .icon-users { background-color: #3498db; }
        .icon-tasks { background-color: #f39c12; }
        .icon-completed { background-color: #2ecc71; }
        .icon-active { background-color: #e74c3c; }

        .stat-info .stat-number {
            font-size: 2rem;
            font-weight: 600;
            color: #2c3e50;
            line-height: 1;
        }

        .stat-info .stat-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            font-weight: 500;
        }

        /* --- NAVIGASI ADMIN --- */
        .admin-nav {
            margin-bottom: 2.5rem;
        }
        
        .admin-nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            background: #ffffff;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.04);
        }

        .admin-nav-links a {
            flex: 1 1 180px;
            text-decoration: none;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-nav-primary { background: #e8eaf6; color: #3f51b5; }
        .btn-nav-primary:hover { background: #3f51b5; color: #fff; }

        .btn-nav-secondary { background: #fff3e0; color: #f57c00; }
        .btn-nav-secondary:hover { background: #f57c00; color: #fff; }

        .btn-nav-success { background: #e8f5e9; color: #388e3c; }
        .btn-nav-success:hover { background: #388e3c; color: #fff; }
        
        .btn-nav-warning { background: #fffde7; color: #fbc02d; }
        .btn-nav-warning:hover { background: #fbc02d; color: #fff; }

        /* --- TABEL PENGGUNA --- */
        .users-table-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .table-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .table-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        thead th {
            background-color: #f8f9fa;
            color: #555;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background-color: #f9f9fc;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }

        .btn-edit {
            background-color: #e0e0e0;
            color: #555;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-edit:hover {
            background-color: #3498db;
            color: white;
        }

    </style>
</head>
<body>
    
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h2><i class="fa-solid fa-shield-halved"></i> Admin Panel</h2>
            </div>
            <div class="nav-menu">
                <div class="nav-user">
                    <span class="user-greeting">
                        Admin: <strong><?= htmlspecialchars(getCurrentUser()['full_name']) ?></strong>
                    </span>
                    <div class="nav-actions">
                         <a href="../logout.php" class="nav-link logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="container">
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-users"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?= $stats['total_users'] ?></div>
                    <div class="stat-label">Total Pengguna</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-tasks"><i class="fa-solid fa-clipboard-list"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?= $stats['total_tasks'] ?></div>
                    <div class="stat-label">Total Tugas</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-completed"><i class="fa-solid fa-check-double"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?= $stats['completed_tasks'] ?></div>
                    <div class="stat-label">Tugas Selesai</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon icon-active"><i class="fa-solid fa-user-clock"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?= $stats['active_users'] ?></div>
                    <div class="stat-label">Pengguna Aktif</div>
                </div>
            </div>
        </section>

        <section class="admin-nav">
            <div class="admin-nav-links">
                <a href="users.php" class="btn-nav-primary"><i class="fa-solid fa-users-cog"></i> Kelola Pengguna</a>
                <a href="tasks.php" class="btn-nav-secondary"><i class="fa-solid fa-folder-open"></i> Lihat Semua Tugas</a>
                <a href="reports.php" class="btn-nav-warning"><i class="fa-solid fa-chart-pie"></i> Laporan</a>
                <a href="settings.php" class="btn-nav-success"><i class="fa-solid fa-gears"></i> Pengaturan</a>
            </div>
        </section>

        <section class="users-table-wrapper">
            <div class="table-header">
                <h3><i class="fa-solid fa-user-plus"></i> Pengguna Terbaru</h3>
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
                        <?php if (!empty($recent_users)): ?>
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
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn-edit">Edit</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem;">Belum ada pengguna terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>
</html>