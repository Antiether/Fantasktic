<?php include 'includes/header.php'; ?>

<div class="header">
    <div class="container">
        <h1>Tambah Tugas Baru</h1>
        <p>Buat tugas baru dengan deadline dan prioritas yang jelas</p>
    </div>
</div>

<div class="container">
    <div class="form-container">
        <div class="form-header">
            <h2>Detail Tugas</h2>
            <p>Isi informasi lengkap untuk tugas baru Anda</p>
        </div>

        <form action="save_task.php" method="post" class="task-form">
            <div class="form-group">
                <label for="title" class="form-label">
                    Judul Tugas
                    <span class="required">*</span>
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       class="form-input" 
                       placeholder="Masukkan judul tugas..."
                       required
                       maxlength="100">
                <small class="form-help">Berikan judul yang jelas dan deskriptif</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="deadline" class="form-label">
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
                        Prioritas
                        <span class="required">*</span>
                    </label>
                    <select id="priority" name="priority" class="form-select" required>
                        <option value="">Pilih prioritas...</option>
                        <option value="Rendah" data-color="#28a745">Rendah</option>
                        <option value="Sedang" data-color="#ffc107" selected>Sedang</option>
                        <option value="Tinggi" data-color="#dc3545">Tinggi</option>
                    </select>
                    <small class="form-help">Seberapa penting tugas ini?</small>
                </div>
            </div>

            <div class="form-group">
                <label for="subtasks" class="form-label">
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
                    Simpan Tugas
                </button>
                <a href="index.php" class="btn btn-secondary btn-large">
                    Kembali
                </a>
            </div>
        </form>

<script>
// Form validation enhancement
document.querySelector('.task-form').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const deadline = document.getElementById('deadline').value;
    const priority = document.getElementById('priority').value;

    if (!title) {
        alert('Judul tugas tidak boleh kosong!');
        document.getElementById('title').focus();
        e.preventDefault();
        return;
    }

    if (!deadline) {
        alert('Deadline harus ditentukan!');
        document.getElementById('deadline').focus();
        e.preventDefault();
        return;
    }

    if (!priority) {
        alert('Prioritas harus dipilih!');
        document.getElementById('priority').focus();
        e.preventDefault();
        return;
    }

    const deadlineDate = new Date(deadline);
    const now = new Date();
    if (deadlineDate < now) {
        const confirm = window.confirm('Deadline yang dipilih sudah berlalu. Apakah Anda yakin ingin melanjutkan?');
        if (!confirm) {
            document.getElementById('deadline').focus();
            e.preventDefault();
            return;
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>