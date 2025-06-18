<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

$task_id = (int)($_GET['id'] ?? 0);
$user_id = getCurrentUserId();

if ($task_id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $db = getDB();

    // Fetch task
    $stmt = $db->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch();

    if (!$task) {
        header('Location: index.php?error=task_not_found');
        exit;
    }

// Fetch subtasks with is_done status
$subtask_stmt = $db->prepare("SELECT id, text, is_done FROM subtasks WHERE task_id = ? ORDER BY sort_order ASC");
$subtask_stmt->execute([$task_id]);
$subtasks = $subtask_stmt->fetchAll();

} catch (Exception $e) {
    $error = "Terjadi kesalahan saat memuat data tugas.";
}
?>

<?php include 'includes/header.php'; ?>

<div class="header">
    <div class="container">
        <h1>✏️ Edit Tugas</h1>
        <p>Perbarui detail tugas Anda dengan deadline dan prioritas yang jelas</p>
    </div>
</div>

<div class="container">
    <div class="form-container">
        <div class="form-header">
            <h2>📝 Detail Tugas</h2>
            <p>Perbarui informasi lengkap untuk tugas Anda</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="update_task.php" method="post" class="task-form">
            <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id']) ?>">

            <div class="form-group">
                <label for="title" class="form-label">
                    <span class="label-icon">📋</span>
                    Judul Tugas
                    <span class="required">*</span>
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       class="form-input" 
                       placeholder="Masukkan judul tugas..."
                       required
                       maxlength="255"
                       value="<?= htmlspecialchars($task['title']) ?>">
                <small class="form-help">Berikan judul yang jelas dan deskriptif</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="deadline" class="form-label">
                        <span class="label-icon">📅</span>
                        Deadline
                        <span class="required">*</span>
                    </label>
                    <input type="datetime-local" 
                           id="deadline" 
                           name="deadline" 
                           class="form-input"
                           required
                           min="<?= date('Y-m-d\TH:i') ?>"
                           value="<?= date('Y-m-d\TH:i', strtotime($task['deadline'])) ?>">
                    <small class="form-help">Tentukan kapan tugas ini harus selesai</small>
                </div>

                <div class="form-group">
                    <label for="priority" class="form-label">
                        <span class="label-icon">⚡</span>
                        Prioritas
                        <span class="required">*</span>
                    </label>
                    <select id="priority" name="priority" class="form-select" required>
                        <option value="">Pilih prioritas...</option>
                        <option value="Rendah" <?= $task['priority'] === 'Rendah' ? 'selected' : '' ?>>🟢 Rendah</option>
                        <option value="Sedang" <?= $task['priority'] === 'Sedang' ? 'selected' : '' ?>>🟡 Sedang</option>
                        <option value="Tinggi" <?= $task['priority'] === 'Tinggi' ? 'selected' : '' ?>>🔴 Tinggi</option>
                    </select>
                    <small class="form-help">Seberapa penting tugas ini?</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <span class="label-icon">☑️</span>
                    Sub-tugas (Checklist)
                    <span class="optional">opsional</span>
                </label>
                <div class="subtask-list">
                    <?php if (!empty($subtasks)): ?>
                        <ul style="list-style: none; padding-left: 0;">
                            <?php foreach ($subtasks as $subtask): ?>
                                <li style="margin-bottom: 0.5rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" name="subtasks_done[<?= $subtask['id'] ?>]" value="1" <?= $subtask['is_done'] ? 'checked' : '' ?>>
                                        <span><?= htmlspecialchars($subtask['text']) ?></span>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Tidak ada sub-tugas.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <span class="btn-icon">💾</span>
                    Perbarui Tugas
                </button>
                <a href="index.php" class="btn btn-secondary btn-large">
                    <span class="btn-icon">↩️</span>
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
