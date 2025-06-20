<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$user_id = (int)($_GET['id'] ?? 0);

if ($user_id <= 0) {
    header('Location: users.php');
    exit;
}

try {
    $db = getDB();

    // Fetch user data
    $stmt = $db->prepare("SELECT id, username, full_name, email, role, is_active FROM users WHERE id = ? AND role = 'user'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: users.php?error=user_not_found');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($full_name) || empty($email)) {
            $error = "Nama lengkap dan email tidak boleh kosong.";
        } else {
            $update_stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, is_active = ?, updated_at = NOW() WHERE id = ? AND role = 'user'");
            $update_stmt->execute([$full_name, $email, $is_active, $user_id]);
            $success = "Data pengguna berhasil diperbarui.";

            // Refresh user data to show the latest info on the form
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    }
} catch (Exception $e) {
    $error = "Terjadi kesalahan saat memuat data pengguna.";
    // die($e->getMessage()); // Uncomment for debugging
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna - Admin Fantasktic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        /* Menggunakan Font dari Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        /* --- STYLING UTAMA (Diambil dari Halaman Sebelumnya) --- */
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

        /* --- HEADER/NAVBAR YANG KONSISTEN --- */
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
        .user-info a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        .user-info a:hover {
            opacity: 0.8;
        }

        /* --- STYLING FORM BARU YANG KONSISTEN --- */
        .form-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
        }
        
        .form-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .form-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-body {
            padding: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #34495e;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #dcdcdc;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        
        .form-input:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        input[type="checkbox"] {
            width: 1.2em;
            height: 1.2em;
            accent-color: #667eea;
            cursor: pointer;
        }
        
        small {
            color: #7f8c8d;
            font-size: 0.85rem;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end; /* Menggeser tombol ke kanan */
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn {
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
            text-align: center;
        }
        
        .btn-primary {
            background-color: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background-color: #5a6ed0;
        }
        
        .btn-secondary {
            background-color: #e9ecef;
            color: #495057;
            border: 1px solid #ced4da;
        }
        .btn-secondary:hover {
            background-color: #dae0e5;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
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
            
            <a href="users.php"><i class="fa-solid fa-users"></i> Admin</a>
            <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <main class="container">
        <div class="form-wrapper">
            <div class="form-header">
                <h3>✏️ Edit Pengguna</h3>
            </div>
            <div class="form-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php elseif (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="post" action="edit_user.php?id=<?= $user['id'] ?>">
                    <div class="form-group">
                        <label>Username (tidak dapat diubah)</label>
                        <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="full_name">Nama Lengkap</label>
                        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="is_active">Status</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_active" name="is_active" <?= $user['is_active'] ? 'checked' : '' ?>>
                            <small>Centang untuk mengaktifkan, hilangkan centang untuk menonaktifkan.</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="users.php" class="btn btn-secondary">Kembali</a>
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