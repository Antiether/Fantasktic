<?php
require_once __DIR__ . '/../config/session.php';

// Check if user is logged in for protected pages
$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['login.php', 'register.php'];

if (!in_array($current_page, $public_pages)) {
    requireLogin();
}

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fantasktic - Task Management</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h2>🚀 Fantasktic</h2>
            </div>
            <div class="nav-menu">
                <div class="nav-user">
                    <span class="user-greeting">
                        Halo, <strong><?= htmlspecialchars($current_user['full_name']) ?></strong>
                        <?php if (isAdmin()): ?>
                            <span class="admin-badge">Admin</span>
                        <?php endif; ?>
                    </span>
                    <div class="nav-actions">
                        <a href="index.php" class="nav-link">📋 Tasks</a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/dashboard.php" class="nav-link">⚙️ Admin</a>
                        <?php endif; ?>
                        <a href="logout.php" class="nav-link logout-link">🚪 Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

<style>
.navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 0;
}

.navbar .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-brand h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.nav-user {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-greeting {
    font-size: 0.95rem;
}

.admin-badge {
    background: #ffc107;
    color: #212529;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

.nav-actions {
    display: flex;
    gap: 0.5rem;
}

.nav-link {
    color: white;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
}

.nav-link:hover {
    background: rgba(255,255,255,0.2);
}

.logout-link:hover {
    background: #dc3545;
}

@media (max-width: 768px) {
    .navbar .container {
        flex-direction: column;
        gap: 1rem;
    }
    
    .nav-user {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .nav-actions {
        justify-content: center;
    }
}
</style>
