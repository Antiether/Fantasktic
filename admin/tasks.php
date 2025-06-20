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
    // die($e->getMessage()); // For debugging
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas - Admin Fantasktic</title>
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

        /* --- STYLING TABEL KARTU --- */
        .table-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .table-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .table-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .table-content { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; }
        
        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
            white-space: nowrap; /* Mencegah teks turun baris */
        }
        
        thead th {
            background-color: #f8f9fa;
            color: #555;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background-color: #f9f9fc; }

        /* --- BADGE PRIORITAS --- */
        .priority-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
            text-transform: capitalize;
        }
        .priority-tinggi { background-color: #e74c3c; } /* Merah */
        .priority-sedang { background-color: #f39c12; } /* Oranye */
        .priority-rendah { background-color: #3498db; } /* Biru */

        /* --- TOMBOL AKSI --- */
        .btn-delete {
            background-color: #e74c3c;
            color: white;
            padding: 0.4rem 0.9rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-delete:hover { background-color: #c0392b; }

        /* Footer */
        .footer {
            background: #667eea;
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: auto;
        }
    </style>
</head>
<body>
    
    <header class="header-main">
        <div class="brand">✨ Fantasktic</div>
        <div class="user-info">
            <a href="dashboard.php"><i class="fa-solid fa-tachograph-digital"></i> Dashboard</a>
            <a href="users.php"><i class="fa-solid fa-users"></i> Kelola Pengguna</a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <main class="container">
        <div class="table-wrapper">
            <div class="table-header">
                <h3><i class="fa-solid fa-folder-open"></i> Daftar Semua Tugas</h3>
            </div>
            <div class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul Tugas</th>
                            <th>Pengguna</th>
                            <th>Deadline</th>
                            <th>Prioritas</th>
                            <th>Dibuat Pada</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tasks)): ?>
                            <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?= $task['id'] ?></td>
                                <td><?= htmlspecialchars($task['title']) ?></td>
                                <td><?= htmlspecialchars($task['full_name']) ?> (<?= htmlspecialchars($task['username']) ?>)</td>
                                <td><?= date('d M Y, H:i', strtotime($task['deadline'])) ?></td>
                                <td>
                                    <?php 
                                        $priority_class = 'priority-' . strtolower(htmlspecialchars($task['priority']));
                                    ?>
                                    <span class="priority-badge <?= $priority_class ?>">
                                        <?= htmlspecialchars($task['priority']) ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y', strtotime($task['created_at'])) ?></td>
                                <td>
                                    <form action="delete_task.php" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini? Tindakan ini tidak dapat diurungkan.');">
                                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                        <button type="submit" class="btn-delete">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem;">Belum ada tugas yang dibuat.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Fantasktic. All rights reserved.</p>
    </footer>

</body>
</html>