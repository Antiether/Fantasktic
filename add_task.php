<?php 
require_once 'config/database.php';
include 'includes/header.php'; 
?>

<div class="header">
    <div class="container">
        <h1>➕ Tambah Tugas Baru</h1>
        <p>Buat tugas baru dengan deadline dan prioritas yang jelas</p>
    </div>
</div>

<div class="container">
    <div class="form-container">
        <div class="form-header">
            <h2>📝 Detail Tugas</h2>
            <p>Isi informasi lengkap untuk tugas baru Anda</p>
        </div>

        <form action="save_task.php" method="post" class="task-form">
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
                       maxlength="255">
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
                           min="<?= date('Y-m-d\TH:i') ?>">
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
                        <option value="Rendah">🟢 Rendah</option>
                        <option value="Sedang" selected>🟡 Sedang</option>
                        <option value="Tinggi">🔴 Tinggi</option>
                    </select>
                    <small class="form-help">Seberapa penting tugas ini?</small>
                </div>
            </div>

            <div class="form-group">
                <label for="subtasks" class="form-label">
                    <span class="label-icon">☑️</span>
                    Sub-tugas (Checklist)
                    <span class="optional">opsional</span>
                </label>
                <div class="subtask-input-container">
                    <textarea id="subtasks" 
                              name="subtasks" 
                              class="form-textarea" 
                              rows="6" 
                              placeholder="Contoh:&#10;Riset dan kumpulkan referensi&#10;Buat outline atau kerangka&#10;Tulis draft pertama&#10;Review dan edit&#10;Finalisasi dokumen"></textarea>
                    <div class="textarea-help">
                        <small class="form-help">
                            💡 <strong>Tips:</strong> Tulis satu sub-tugas per baris untuk membuat checklist yang mudah diikuti
                        </small>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <span class="btn-icon">💾</span>
                    Simpan Tugas
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
