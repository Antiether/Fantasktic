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
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, username, email, password, full_name, role, is_active FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && $user['is_active'] && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = 'Username/email atau password salah!';
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
    <title>Login - Fantasktic</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
        }

        .login-container {
            position: relative;
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            padding: 3rem 2.5rem 2.5rem;
            animation: fadeInUp 0.8s ease forwards;
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: #2c3e50;
            font-size: 2.25rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .login-header p {
            color: #6c757d;
            font-size: 1.1rem;
            font-weight: 400;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: #34495e;
            margin-bottom: 0.4rem;
            font-size: 0.95rem;
        }

        .label-icon {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        .form-input {
            width: 100%;
            padding: 0.65rem 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 8px rgba(102, 126, 234, 0.6);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-size: 1.1rem;
            transition: background 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.6);
            width: 100%;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6edb 0%, #6f3f9e 100%);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.8);
        }

        .btn-icon {
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }

        .demo-accounts {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            box-shadow: inset 0 0 10px #bbdefb;
        }

        .demo-accounts h4 {
            color: #1976d2;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .demo-account {
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            font-weight: 500;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #6c757d;
            font-size: 1rem;
            font-weight: 400;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #5a6edb;
            text-decoration: underline;
        }

        /* Background pattern */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-image: radial-gradient(circle at center, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>
    <body>
        <div class="login-container">
            <div>
                <div class="login-header">
                    <h1>🚀 Fantasktic</h1>
                    <p>Masuk ke akun Anda</p>
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
                
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <span class="label-icon">👤</span>
                            Username atau Email
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-input" 
                               placeholder="Masukkan username atau email"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
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
                               placeholder="Masukkan password"
                               required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large" style="width: 100%; margin-top: 1rem;">
                        <span class="btn-icon">🚪</span>
                        Masuk
                    </button>
                </form>
                
                <div class="demo-accounts">
                    <h4>🧪 Akun Demo:</h4>
                    <div class="demo-account">
                        <strong>Admin:</strong> admin / admin123
                    </div>
                    <div class="demo-account">
                        <strong>User:</strong> testuser / user123
                    </div>
                </div>
                
                <div class="register-link">
                    <p>
                        Belum punya akun? 
                        <a href="register.php">
                            Daftar di sini
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
