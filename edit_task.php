<?php include 'includes/header.php';

if (!isset($_GET['id'])) {
  echo '<div class="container"><div class="error-message">
    <h2>❌ Error</h2>
    <p>ID tugas tidak valid atau tidak ditemukan.</p>
    <a href="index.php" class="btn btn-primary">Kembali ke Daftar Tugas</a>
  </div></div>';
  include 'includes/footer.php';
  exit;
}

$id = (int)$_GET['id'];
$tasks_file = 'data/tasks.json';

if (!file_exists($tasks_file)) {
  echo '<div class="container"><div class="error-message">
    <h2>❌ Error</h2>
    <p>File data tugas tidak ditemukan.</p>
    <a href="index.php" class="btn btn-primary">Kembali ke Daftar Tugas</a>
  </div></div>';
  include 'includes/footer.php';
  exit;
}

$tasks = json_decode(file_get_contents($tasks_file), true);

if (!isset($tasks[$id])) {
  echo '<div class="container"><div class="error-message">
    <h2>❌ Error</h2>
    <p>Tugas dengan ID tersebut tidak ditemukan.</p>
    <a href="index.php" class="btn btn-primary">Kembali ke Daftar Tugas</a>
  </div></div>';
  include 'includes/footer.php';
  exit;
}

$task = $tasks[$id];

// Map old priority values to new ones
$priority_map = [
  'Low' => 'Rendah',
  'Medium' => 'Sedang', 
  'High' => 'Tinggi'
];

$current_priority = isset($priority_map[$task['priority']]) ? $priority_map[$task['priority']] : $task['priority'];

// Calculate task progress
$total_subtasks = count($task['subtasks']);
$completed_subtasks = array_reduce($task['subtasks'], function($carry, $subtask) {
    return $carry + ($subtask['done'] ? 1 : 0);
}, 0);
$progress_percentage = $total_subtasks > 0 ? ($completed_subtasks / $total_subtasks) * 100 : 0;
?>

<div class="header">
    <div class="container">
        <h1>✏️ Edit Tugas</h1>
        <p>Perbarui informasi tugas yang sudah ada</p>
    </div>
</div>

<div class="container">
    <div class="form-container">
        <!-- Current Task Info -->
        <div class="current-task-info">
            <h3>📋 Tugas Saat Ini</h3>
            <div class="current-task-preview">
                <div class="current-task-title"><?= htmlspecialchars($task['title']) ?></div>
                <div class="current-task-meta">
                    <span class="current-deadline">📅 <?= date('d M Y H:i', strtotime($task['deadline'])) ?></span>
                    <span class="task-priority priority-<?= strtolower($current_priority) ?>"><?= $current_priority ?></span>
                </div>
                <?php if ($total_subtasks > 0): ?>
                    <div class="current-progress">
                        <small>Progress: <?= $completed_subtasks ?>/<?= $total_subtasks ?> selesai (<?= round($progress_percentage) ?>%)</small>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $progress_percentage ?>%;"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-header">
            <h2>🔄 Form Edit</h2>
            <p>Ubah informasi yang diperlukan di bawah ini</p>
        </div>

        <form action="save_task.php" method="post" class="task-form" id="editTaskForm">
            <input type="hidden" name="edit_id" value="<?= $id ?>">

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
                       value="<?= htmlspecialchars($task['title']) ?>"
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
                           value="<?= date('Y-m-d\TH:i', strtotime($task['deadline'])) ?>"
                           required>
                    <small class="form-help">Kapan tugas ini harus selesai?</small>
                </div>

                <div class="form-group">
                    <label for="priority" class="form-label">
                        <span class="label-icon">⚡</span>
                        Prioritas
                        <span class="required">*</span>
                    </label>
                    <select id="priority" name="priority" class="form-select" required>
                        <option value="">Pilih prioritas...</option>
                        <option value="Rendah" <?= $current_priority == 'Rendah' ? 'selected' : '' ?>>🟢 Rendah</option>
                        <option value="Sedang" <?= $current_priority == 'Sedang' ? 'selected' : '' ?>>🟡 Sedang</option>
                        <option value="Tinggi" <?= $current_priority == 'Tinggi' ? 'selected' : '' ?>>🔴 Tinggi</option>
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
                              placeholder="Contoh:&#10;Riset dan kumpulkan referensi&#10;Buat outline atau kerangka&#10;Tulis draft pertama&#10;Review dan edit&#10;Finalisasi dokumen"><?php
                        foreach ($task['subtasks'] as $sub) {
                            echo htmlspecialchars($sub['text']) . "\n";
                        }
                    ?></textarea>
                    <div class="textarea-help">
                        <small class="form-help">
                            💡 <strong>Catatan:</strong> Mengubah sub-tugas akan mereset status checklist yang sudah ada
                        </small>
                    </div>
                </div>
            </div>

            <!-- Show existing checklist status -->
            <?php if ($total_subtasks > 0): ?>
                <div class="existing-subtasks">
                    <h4>📝 Status Sub-tugas Saat Ini</h4>
                    <div class="subtasks-readonly">
                        <?php foreach ($task['subtasks'] as $sub): ?>
                            <div class="subtask-item-readonly">
                                <span class="checkbox-status <?= $sub['done'] ? 'checked' : 'unchecked' ?>">
                                    <?= $sub['done'] ? '✅' : '⬜' ?>
                                </span>
                                <span class="subtask-text <?= $sub['done'] ? 'completed' : '' ?>">
                                    <?= htmlspecialchars($sub['text']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="form-help" style="color: #ffc107;">
                        ⚠️ Status checklist akan direset jika Anda mengubah daftar sub-tugas
                    </small>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <span class="btn-icon">💾</span>
                    Update Tugas
                </button>
                <a href="index.php" class="btn btn-secondary btn-large">
                    <span class="btn-icon">↩️</span>
                    Kembali
                </a>
                <button type="button" class="btn btn-warning btn-large" onclick="resetForm()">
                    <span class="btn-icon">🔄</span>
                    Reset
                </button>
            </div>

            <!-- Change Detection -->
            <div class="change-indicator" id="changeIndicator" style="display: none;">
                <span class="change-icon">⚠️</span>
                <span class="change-text">Ada perubahan yang belum disimpan</span>
            </div>
        </form>

        <!-- Live Preview Section -->
        <div class="form-preview" id="taskPreview">
            <h3>👀 Preview Perubahan</h3>
            <div class="preview-card">
                <div class="preview-title" id="previewTitle"><?= htmlspecialchars($task['title']) ?></div>
                <div class="preview-meta">
                    <span id="previewDeadline">📅 <?= date('d M Y H:i', strtotime($task['deadline'])) ?></span>
                    <span id="previewPriority" class="task-priority priority-<?= strtolower($current_priority) ?>"><?= $current_priority ?></span>
                </div>
                <div class="preview-subtasks" id="previewSubtasks">
                    <?php if ($total_subtasks > 0): ?>
                        <div style="margin-top: 1rem;">
                            <small style="color: #6c757d; font-weight: 600;">Sub-tugas:</small>
                            <?php foreach ($task['subtasks'] as $sub): ?>
                                <div class="subtask-item">
                                    <input type="checkbox" disabled style="margin-right: 0.5rem;">
                                    <span><?= htmlspecialchars($sub['text']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Store original values for change detection
const originalValues = {
    title: document.getElementById('title').value,
    deadline: document.getElementById('deadline').value,
    priority: document.getElementById('priority').value,
    subtasks: document.getElementById('subtasks').value
};

function detectChanges() {
    const currentValues = {
        title: document.getElementById('title').value,
        deadline: document.getElementById('deadline').value,
        priority: document.getElementById('priority').value,
        subtasks: document.getElementById('subtasks').value
    };
    
    const hasChanges = Object.keys(originalValues).some(key => 
        originalValues[key] !== currentValues[key]
    );
    
    const indicator = document.getElementById('changeIndicator');
    if (hasChanges) {
        indicator.style.display = 'flex';
    } else {
        indicator.style.display = 'none';
    }
    
    return hasChanges;
}

function updatePreview() {
    const title = document.getElementById('title').value.trim();
    const deadline = document.getElementById('deadline').value;
    const priority = document.getElementById('priority').value;
    const subtasks = document.getElementById('subtasks').value.trim();
    
    // Update title
    document.getElementById('previewTitle').textContent = title || '<?= htmlspecialchars($task['title']) ?>';
    
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
    }
    
    // Update priority
    const priorityElement = document.getElementById('previewPriority');
    if (priority) {
        priorityElement.textContent = priority;
        priorityElement.className = 'task-priority priority-' + priority.toLowerCase();
    }
    
    // Update subtasks
    const subtasksContainer = document.getElementById('previewSubtasks');
    if (subtasks) {
        const subtaskList = subtasks.split('\n').filter(task => task.trim());
        let subtaskHTML = '<div style="margin-top: 1rem;"><small style="color: #6c757d; font-weight: 600;">Sub-tugas (Baru):</small>';
        subtaskList.forEach(task => {
            subtaskHTML += `<div class="subtask-item"><input type="checkbox" disabled style="margin-right: 0.5rem;"><span>${task.trim()}</span></div>`;
        });
        subtaskHTML += '</div>';
        subtasksContainer.innerHTML = subtaskHTML;
    } else {
        subtasksContainer.innerHTML = '<div style="margin-top: 1rem;"><small style="color: #6c757d;">Tidak ada sub-tugas</small></div>';
    }
    
    detectChanges();
}

function resetForm() {
    if (detectChanges()) {
        const confirm = window.confirm('🔄 Apakah Anda yakin ingin mereset semua perubahan?');
        if (confirm) {
            document.getElementById('title').value = originalValues.title;
            document.getElementById('deadline').value = originalValues.deadline;
            document.getElementById('priority').value = originalValues.priority;
            document.getElementById('subtasks').value = originalValues.subtasks;
            updatePreview();
        }
    }
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const deadlineInput = document.getElementById('deadline');
    const prioritySelect = document.getElementById('priority');
    const subtasksTextarea = document.getElementById('subtasks');
    
    titleInput.addEventListener('input', updatePreview);
    deadlineInput.addEventListener('change', updatePreview);
    prioritySelect.addEventListener('change', updatePreview);
    subtasksTextarea.addEventListener('input', updatePreview);
});

// Form submission validation
document.getElementById('editTaskForm').addEventListener('submit', function(e) {
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
    
    // Confirm if user wants to proceed with changes
    if (detectChanges()) {
        const confirm = window.confirm('💾 Apakah Anda yakin ingin menyimpan perubahan?');
        if (!confirm) {
            e.preventDefault();
            return;
        }
    }
});

// Warn user about unsaved changes when leaving page
window.addEventListener('beforeunload', function(e) {
    if (detectChanges()) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});
</script>

<?php include 'includes/footer.php'; ?>