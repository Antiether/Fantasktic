<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

try {
    $db = getDB();

    // Fetch all users with role 'user'
    $stmt = $db->prepare("SELECT id, username, full_name, email, is_active, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll();

} catch (Exception $e) {
    // die($e->getMessage()); // Uncomment for debugging
    $error = "Terjadi kesalahan saat memuat data pengguna.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin Fantasktic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        /* Menggunakan Font dari Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        /* --- STYLING UTAMA (Diambil dari Dashboard) --- */
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
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* --- HEADER/NAVBAR YANG KONSISTEN --- */
        /* Menggunakan desain header dari screenshot */
        .header-main {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            padding: 1.2rem 1.5rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header-main .brand {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .header-main .user-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .user-info .greeting span {
            background-color: rgba(255,255,255,0.2);
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .user-info a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        .user-info a:hover {
            opacity: 0.8;
        }

        /* --- TABEL PENGGUNA --- */
        .users-table-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            margin-top: 1rem;
        }

        .table-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
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
        
        /* Menggunakan style status dari gambar */
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
            text-transform: capitalize;
        }

        .status-active { background-color: #28a745; }
        .status-inactive { background-color: #dc3545; }
        
        /* Menggunakan style tombol dari gambar */
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
            padding: 0.4rem 0.9rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-edit:hover {
            background-color: #e0a800;
            color: #212529;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        /* Footer */
        .footer {
            background: #667eea;
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: auto; /* Mendorong footer ke bawah */
        }

    </style>
</head>
<body>
    
    <header class="header-main">
        <div class="brand">✨ Fantasktic</div>
        <div class="user-info">
             <div class="greeting">
                Halo, <strong><?= htmlspecialchars(getCurrentUser()['full_name']) ?></strong>
                <span style="background-color: #ffc107; color: #333; font-weight: bold; margin-left: 8px; padding: 0.2rem 0.6rem; border-radius: 8px;">Admin</span>
            </div>
            
            <a href="dashboard.php"><i class="fa-solid fa-users"></i> Admin</a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <main class="container">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <section class="users-table-wrapper">
            <div class="table-header">
                <h3><i class="fa-solid fa-users-cog"></i> Kelola Pengguna</h3>
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
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
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
    
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Fantasktic. All rights reserved.</p>
    </footer>

</body>
</html>