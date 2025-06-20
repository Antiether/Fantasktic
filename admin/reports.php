<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

try {
    $db = getDB();
    $stats = [];

    // 1. Statistik Total Pengguna
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $stats['total_users'] = $stmt->fetch()['total'] ?? 0;
    
    // 2. Statistik Total Tugas
    $stmt = $db->query("SELECT COUNT(*) as total FROM tasks");
    $stats['total_tasks'] = $stmt->fetch()['total'] ?? 0;
    
    // 3. Statistik Tugas Selesai
    $stmt = $db->query("SELECT COUNT(DISTINCT t.id) as total FROM tasks t 
                        LEFT JOIN subtasks st ON t.id = st.task_id 
                        WHERE t.id NOT IN (SELECT DISTINCT task_id FROM subtasks WHERE is_done = 0)
                        AND t.id IN (SELECT DISTINCT task_id FROM subtasks)");
    $stats['completed_tasks'] = $stmt->fetch()['total'] ?? 0;
    
    // 4. Statistik Pengguna Aktif (pengguna yang memiliki setidaknya 1 tugas)
    $stmt = $db->query("SELECT COUNT(DISTINCT user_id) as total FROM tasks");
    $stats['active_users'] = $stmt->fetch()['total'] ?? 0;
    
    // 5. Ambil 5 Pengguna Terbaru
    $stmt = $db->prepare("SELECT id, username, full_name, email, created_at, is_active FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_users = $stmt->fetchAll();
    
} catch (Exception $e) {
    // die($e->getMessage()); // Uncomment untuk debugging
    $error = "Terjadi kesalahan saat memuat data dashboard.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fantasktic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        /* Menggunakan Font dari Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        /* --- STYLING UTAMA (KONSISTEN DI SEMUA HALAMAN) --- */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f9;
            color: #34495e;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main.container {
            flex: 1;
            padding: 2rem 1.5rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 2rem;
        }

        /* --- HEADER/NAVBAR --- */
        .header-main {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            padding: 1.2rem 1.5rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header-main .brand { font-size: 1.5rem; font-weight: 700; }
        .header-main .user-info { display: flex; align-items: center; gap: 1.5rem; }
        .user-info a { color: white; text-decoration: none; font-weight: 500; transition: opacity 0.3s; }
        .user-info a:hover { opacity: 0.8; }

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
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08); }
        .stat-icon {
            font-size: 1.8rem; width: 60px; height: 60px;
            display: grid; place-items: center;
            border-radius: 50%; color: #fff;
        }
        .icon-users { background-color: #3498db; }
        .icon-tasks { background-color: #f39c12; }
        .icon-completed { background-color: #2ecc71; }
        .icon-active { background-color: #e74c3c; }
        .stat-info .stat-number { font-size: 2rem; font-weight: 600; color: #2c3e50; line-height: 1; }
        .stat-info .stat-label { font-size: 0.9rem; color: #7f8c8d; font-weight: 500; }
        
        /* --- NAVIGASI CEPAT --- */
        .admin-nav { margin-bottom: 2.5rem; }
        .admin-nav-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            background: #ffffff;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.04);
        }
        .admin-nav-links a {
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
            color: #3f51b5;
            background: #e8eaf6;
        }
        .admin-nav-links a:hover { background: #3f51b5; color: #fff; }

        /* --- STYLING TABEL KARTU --- */
        .table-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        .table-header { padding: 1.2rem 1.5rem; border-bottom: 1px solid #e0e0e0; }
        .table-header h3 {
            margin: 0; font-size: 1.3rem; font-weight: 600; color: #2c3e50;
            display: flex; align-items: center; gap: 0.75rem;
        }
        .table-content { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem 1.5rem; text-align: left; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        thead th { background-color: #f8f9fa; color: #555; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background-color: #f9f9fc; }

        /* BADGE STATUS */
        .status-badge {
            padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.8rem;
            font-weight: 600; color: white;
        }
        .status-active { background-color: #28a745; }
        .status-inactive { background-color: #dc3545; }
        
        /* TOMBOL AKSI */
        .btn-edit {
            background-color: #ffc107; color: #212529;
            padding: 0.4rem 0.9rem; border-radius: 6px;
            text-decoration: none; font-size: 0.85rem;
            font-weight: 600; border: none;
            transition: all 0.3s ease;
        }
        .btn-edit:hover { background-color: #e0a800; }
        
        /* Footer */
        .footer { background: #667eea; color: white; text-align: center; padding: 1rem; margin-top: auto; }
    </style>
</head>
<body>
    
    <header class="header-main">
        <div class="brand">✨ Fantasktic</div>
        <div class="user-info">
            <span style="font-weight: 500;">Admin: <?= htmlspecialchars(getCurrentUser()['full_name']) ?></span>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <main class="container">

        <h1>Selamat Datang di Admin Dashboard!</h1>
        
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
                <a href="users.php"><i class="fa-solid fa-users-cog"></i> Kelola Pengguna</a>
                <a href="tasks.php"><i class="fa-solid fa-folder-open"></i> Lihat Semua Tugas</a>
                <a href="reports.php"><i class="fa-solid fa-chart-pie"></i> Laporan Sistem</a>
            </div>
        </section>

        <section class="table-wrapper">
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
                                <td><a href="edit_user.php?id=<?= $user['id'] ?>" class="btn-edit">Edit</a></td>
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
    
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Fantasktic. All rights reserved.</p>
    </footer>

</body>
</html>