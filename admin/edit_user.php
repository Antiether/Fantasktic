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

            // Refresh user data
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    }
} catch (Exception $e) {
    $error = "Terjadi kesalahan saat memuat data pengguna.";
}
?>

<?php include '../includes/header.php'; ?>

<style>
    .container {
        max-width: 600px;
        margin: 2rem auto;
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 6px 30px rgba(0,0,0,0.2);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    h1 {
        text-align: center;
        color: #222;
        margin-bottom: 1.5rem;
        font-weight: 700;
    }
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-weight: 600;
    }
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }
    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }
    form.edit-user-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .form-group {
        display: flex;
        flex-direction: column;
    }
    label {
        font-weight: 600;
        margin-bottom: 0.3rem;
        color: #333;
    }
    .form-input {
        padding: 0.6rem 1rem;
        border: 1.5px solid #ccc;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    .form-input:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }
    input[type="checkbox"] {
        width: auto;
        margin-right: 0.5rem;
        transform: scale(1.2);
        cursor: pointer;
    }
    small {
        color: #666;
        font-size: 0.85rem;
    }
    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 1rem;
    }
    .btn {
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: background-color 0.3s ease;
        text-decoration: none;
        text-align: center;
        display: inline-block;
    }
    .btn-primary {
        background-color: #007bff;
        color: white;
    }
    .btn-primary:hover {
        background-color: #0056b3;
    }
    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }
    .btn-secondary:hover {
        background-color: #565e64;
    }
</style>

<div class="container">
    <h1>✏️ Edit Pengguna</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" action="edit_user.php?id=<?= $user['id'] ?>" class="edit-user-form">
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
            <label for="is_active">Status Aktif</label>
            <input type="checkbox" id="is_active" name="is_active" <?= $user['is_active'] ? 'checked' : '' ?>>
            <small>Centang untuk mengaktifkan pengguna, hilangkan centang untuk menonaktifkan.</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="users.php" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
