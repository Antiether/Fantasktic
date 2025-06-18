<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        try {
            $db = getDB();
            
            // Check if username or email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error = 'Username atau email sudah digunakan!';
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, email, full_name, password, role) VALUES (?, ?, ?, ?, 'user')");
                
                if ($stmt->execute([$username, $email, $full_name, $hashed_password])) {
                    $success = 'Registrasi berhasil! Silakan login dengan akun Anda.';
                } else {
                    $error = 'Terjadi kesalahan saat registrasi. Silakan coba lagi.';
                }
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Fantasktic</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .register-card {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .register-header p {
            color: #6c757d;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>📝 Daftar Akun</h1>
                <p>Buat akun baru untuk menggunakan Fantasktic</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    ❌ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="register-form">
                <div class="form-group">
                    <label for="full_name" class="form-label">
                        <span class="label-icon">👨‍💼</span>
                        Nama Lengkap
                    </label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           class="form-input" 
                           placeholder="Masukkan nama lengkap"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="username" class="form-label">
                        <span class="label-icon">👤</span>
                        Username
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-input" 
                           placeholder="Masukkan username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        <span class="label-icon">📧</span>
                        Email
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input" 
                           placeholder="Masukkan email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <span class="label-icon">🔒</span>
                        Password
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input" 
                           placeholder="Masukkan password (min. 6 karakter)"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">
                        <span class="label-icon">🔒</span>
                        Konfirmasi Password
                    </label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           class="form-input" 
                           placeholder="Ulangi password"
                           required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-large" style="width: 100%; margin-top: 1rem;">
                    <span class="btn-icon">📝</span>
                    Daftar Sekarang
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="color: #6c757d;">
                    Sudah punya akun? 
                    <a href="login.php" style="color: #667eea; text-decoration: none; font-weight: 600;">
                        Login di sini
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
