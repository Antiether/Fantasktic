<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

try {
    $db = getDB();

    // Fetch all users except admins
    $stmt = $db->prepare("SELECT id, username, full_name, email, role, is_active, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll();

} catch (Exception $e) {
    $error = "Terjadi kesalahan saat memuat data pengguna.";
}
?>

<?php include '../includes/header.php'; ?>

<div class="container" style="padding: 2rem 1rem; max-width: 960px; margin: 0 auto;">
    <h1 style="margin-bottom: 1.5rem; font-weight: 700; color: #4a4a4a;">👥 Kelola Pengguna</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom: 1rem;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead style="background: #764ba2; color: white;">
            <tr>
                <th style="padding: 0.75rem; text-align: left;">ID</th>
                <th style="padding: 0.75rem; text-align: left;">Username</th>
                <th style="padding: 0.75rem; text-align: left;">Nama Lengkap</th>
                <th style="padding: 0.75rem; text-align: left;">Email</th>
                <th style="padding: 0.75rem; text-align: left;">Status</th>
                <th style="padding: 0.75rem; text-align: left;">Terdaftar</th>
                <th style="padding: 0.75rem; text-align: left;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr style="border-bottom: 1px solid #ddd; transition: background-color 0.3s;">
                <td style="padding: 0.75rem;"><?= $user['id'] ?></td>
                <td style="padding: 0.75rem;"><?= htmlspecialchars($user['username']) ?></td>
                <td style="padding: 0.75rem;"><?= htmlspecialchars($user['full_name']) ?></td>
                <td style="padding: 0.75rem;"><?= htmlspecialchars($user['email']) ?></td>
                <td style="padding: 0.75rem;">
                    <span class="status-badge <?= $user['is_active'] ? 'status-active' : 'status-inactive' ?>" style="padding: 0.25rem 0.5rem; border-radius: 12px; font-weight: 600; color: white; background-color: <?= $user['is_active'] ? '#28a745' : '#dc3545' ?>;">
                        <?= $user['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </td>
                <td style="padding: 0.75rem;"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                <td style="padding: 0.75rem;">
                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-sm" style="background-color: #ffc107; border: none; color: #212529; padding: 0.375rem 0.75rem; border-radius: 6px; font-weight: 600; text-decoration: none; transition: background-color 0.3s;">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
