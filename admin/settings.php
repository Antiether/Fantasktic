<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

try {
    $db = getDB();

    // For demonstration, let's assume admin can update their own profile info here
    $admin_id = getCurrentUserId();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($full_name) || empty($email)) {
            $error = "Nama lengkap dan email tidak boleh kosong.";
        } else {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ? AND role = 'admin'");
            $stmt->execute([$full_name, $email, $admin_id]);
            $success = "Profil berhasil diperbarui.";
        }
    }

    // Fetch current admin info
    $stmt = $db->prepare("SELECT username, full_name, email FROM users WHERE id = ? AND role = 'admin'");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();

} catch (Exception $e) {
    $error = "Terjadi kesalahan saat memuat data pengaturan.";
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
        background-color:rgb(235, 219, 219);
        color: #721c24;
    }
    .alert-success {
        background-color:rgb(229, 216, 216);
        color: #155724;
    }
    form.settings-form {
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
    .form-actions {
        display: flex;
        justify-content: flex-end;
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
</style>

<div class="container">
    <h1>⚙️ Pengaturan Admin</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" action="settings.php" class="settings-form">
        <div class="form-group">
            <label for="username">Username (tidak dapat diubah)</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($admin['username']) ?>" disabled class="form-input">
        </div>

        <div class="form-group">
            <label for="full_name">Nama Lengkap</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($admin['full_name']) ?>" required class="form-input">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required class="form-input">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
