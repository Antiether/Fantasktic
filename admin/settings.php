<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

try {
    $db = getDB();
    $admin_id = getCurrentUserId();

    // Proses form saat disubmit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($full_name) || empty($email)) {
            $error = "Nama lengkap dan email tidak boleh kosong.";
        } else {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ? AND role = 'admin'");
            $stmt->execute([$full_name, $email, $admin_id]);
            $success = "Profil berhasil diperbarui.";

            // Perbarui juga data di session agar nama di header ikut berubah
            $_SESSION['user']['full_name'] = $full_name;
        }
    }

    // Ambil info admin terbaru untuk ditampilkan di form
    $stmt = $db->prepare("SELECT username, full_name, email FROM users WHERE id = ? AND role = 'admin'");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();

} catch (Exception $e) {
    $error = "Terjadi kesalahan saat memuat data pengaturan.";
    // die($e->getMessage()); // Untuk debugging
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Admin - Fantasktic</title>
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
            display: flex;
            justify-content: center;
            align-items: flex-start;
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

        /* --- STYLING FORM STANDAR --- */
        .form-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
        }
        
        .form-header { padding: 1.2rem 1.5rem; border-bottom: 1px solid #e0e0e0; }
        
        .form-header h3 {
            margin: 0; font-size: 1.3rem; font-weight: 600; color: #2c3e50;
            display: flex; align-items: center; gap: 0.75rem;
        }

        .form-body { padding: 1.5rem; }
        
        .form-group { margin-bottom: 1.25rem; }
        
        label { display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem; color: #34495e; }
        
        .form-input {
            width: 100%; padding: 0.75rem 1rem; border: 1px solid #dcdcdc;
            border-radius: 8px; font-size: 1rem; font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none; border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        .form-input:disabled { background-color: #f5f5f5; cursor: not-allowed; }
        
        .form-actions {
            display: flex; justify-content: flex-end;
            gap: 1rem; margin-top: 1.5rem;
        }

        .btn {
            padding: 0.7rem 1.5rem; border-radius: 8px; font-weight: 600;
            cursor: pointer; border: none; transition: all 0.3s ease;
            text-decoration: none; font-size: 0.9rem; text-align: center;
        }
        
        .btn-primary { background-color: #667eea; color: white; }
        .btn-primary:hover { background-color: #5a6ed0; }

        .btn-secondary {
            background-color: #e9ecef; color: #495057; border: 1px solid #ced4da;
        }
        .btn-secondary:hover { background-color: #dae0e5; }
        
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        .alert-success { background-color: #d1e7dd; color: #0f5132; }
        
        /* Footer */
        .footer { background: #667eea; color: white; text-align: center; padding: 1rem; margin-top: auto; }
    </style>
</head>
<body>
    
    <header class="header-main">
        <div class="brand">✨ Fantasktic</div>
        <div class="user-info">
            <a href="index.php"><i class="fa-solid fa-tachograph-digital"></i> Dashboard</a>
            <a href="users.php"><i class="fa-solid fa-users"></i> Kelola Pengguna</a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <main class="container">
        <div class="form-wrapper">
            <div class="form-header">
                <h3><i class="fa-solid fa-gears"></i> Pengaturan Admin</h3>
            </div>
            <div class="form-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php elseif (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="post" action="settings.php">
                    <div class="form-group">
                        <label for="username">Username (tidak dapat diubah)</label>
                        <input type="text" id="username" value="<?= htmlspecialchars($admin['username'] ?? '') ?>" disabled class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="full_name">Nama Lengkap</label>
                        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>" required class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required class="form-input">
                    </div>

                    <div class="form-actions">
                        <a href="index.php" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Fantasktic. All rights reserved.</p>
    </footer>

</body>
</html>