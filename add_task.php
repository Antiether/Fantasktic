<?php include 'includes/header.php'; ?>

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
                       maxlength="100">
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
                        <option value="Rendah" data-color="#28a745">🟢 Rendah</option>
                        <option value="Sedang" data-color="#ffc107" selected>🟡 Sedang</option>
                        <option value="Tinggi" data-color="#dc3545">🔴 Tinggi</option>
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

        <!-- Preview Section (Optional Enhancement) -->
        <div class="form-preview" id="taskPreview" style="display: none;">
            <h3>👀 Preview Tugas</h3>
            <div class="preview-card">
                <div class="preview-title" id="previewTitle">Judul tugas akan muncul di sini</div>
                <div class="preview-meta">
                    <span id="previewDeadline">📅 Deadline belum ditentukan</span>
                    <span id="previewPriority" class="task-priority">Prioritas belum dipilih</span>
                </div>
                <div class="preview-subtasks" id="previewSubtasks"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Real-time preview (optional enhancement)
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const deadlineInput = document.getElementById('deadline');
    const prioritySelect = document.getElementById('priority');
    const subtasksTextarea = document.getElementById('subtasks');
    const preview = document.getElementById('taskPreview');
    
    function updatePreview() {
        const title = titleInput.value.trim();
        const deadline = deadlineInput.value;
        const priority = prioritySelect.value;
        const subtasks = subtasksTextarea.value.trim();
        
        if (title || deadline || priority || subtasks) {
            preview.style.display = 'block';
            
            // Update title
            document.getElementById('previewTitle').textContent = title || 'Judul tugas akan muncul di sini';
            
            // Update deadline
            if (deadline) {
                const deadlineDate = new Date(deadline);
                const options = { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                document.getElementById('previewDeadline').textContent = '📅 ' + deadlineDate.toLocaleDateString('id-ID', options);
            } else {
                document.getElementById('previewDeadline').textContent = '📅 Deadline belum ditentukan';
            }
            
            // Update priority
            const priorityElement = document.getElementById('previewPriority');
            if (priority) {
                priorityElement.textContent = priority;
                priorityElement.className = 'task-priority priority-' + priority.toLowerCase();
            } else {
                priorityElement.textContent = 'Prioritas belum dipilih';
                priorityElement.className = 'task-priority';
            }
            
            // Update subtasks
            const subtasksContainer = document.getElementById('previewSubtasks');
            if (subtasks) {
                const subtaskList = subtasks.split('\n').filter(task => task.trim());
                let subtaskHTML = '<div style="margin-top: 1rem;"><small style="color: #6c757d; font-weight: 600;">Sub-tugas:</small>';
                subtaskList.forEach(task => {
                    subtaskHTML += `<div class="subtask-item"><input type="checkbox" disabled style="margin-right: 0.5rem;"><span>${task.trim()}</span></div>`;
                });
                subtaskHTML += '</div>';
                subtasksContainer.innerHTML = subtaskHTML;
            } else {
                subtasksContainer.innerHTML = '';
            }
        } else {
            preview.style.display = 'none';
        }
    }
    
    // Add event listeners for real-time preview
    titleInput.addEventListener('input', updatePreview);
    deadlineInput.addEventListener('change', updatePreview);
    prioritySelect.addEventListener('change', updatePreview);
    subtasksTextarea.addEventListener('input', updatePreview);
});

// Form validation enhancement
document.querySelector('.task-form').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const deadline = document.getElementById('deadline').value;
    const priority = document.getElementById('priority').value;
    
    if (!title) {
        alert('⚠️ Judul tugas tidak boleh kosong!');
        document.getElementById('title').focus();
        e.preventDefault();
        return;
    }
    
    if (!deadline) {
        alert('⚠️ Deadline harus ditentukan!');
        document.getElementById('deadline').focus();
        e.preventDefault();
        return;
    }
    
    if (!priority) {
        alert('⚠️ Prioritas harus dipilih!');
        document.getElementById('priority').focus();
        e.preventDefault();
        return;
    }
    
    // Check if deadline is in the past
    const deadlineDate = new Date(deadline);
    const now = new Date();
    if (deadlineDate < now) {
        const confirm = window.confirm('⚠️ Deadline yang dipilih sudah berlalu. Apakah Anda yakin ingin melanjutkan?');
        if (!confirm) {
            document.getElementById('deadline').focus();
            e.preventDefault();
            return;
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>