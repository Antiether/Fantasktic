<?php include 'includes/header.php'; ?>

<div class="header">
    <div class="container">
        <h1>Fantasktic</h1>
        <p>Kelola tugas Anda dengan mudah dan efisien</p>
    </div>
</div>

<div class="container">
    <!-- Search and Filter Section -->
    <div class="search-section">
        <form method="get" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="Cari tugas...">
            <select name="filter" class="filter-select">
                <option value="">Semua Deadline</option>
                <option value="today">Hari Ini</option>
                <option value="week">Minggu Ini</option>
            </select>
            <button type="submit" class="btn btn-primary">Terapkan</button>
            <a href="index.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <!-- Action Section -->
    <div class="action-section">
        <h2 class="section-title">Daftar Tugas</h2>
        <div>
            <a href="add_task.php" class="btn btn-success">Tambah Tugas</a>
        </div>
    </div>

    <!-- Static Task Preview -->
    <div class="task-grid">
        <div class="task priority-tinggi reminder-soon">
            <div class="task-title">Belajar PHP Dasar</div>

            <div class="task-meta">
                <div class="task-deadline">
                    06 Jun 2025 <span style="color: #dc3545; font-weight: bold;">(Hari ini!)</span>
                </div>
                <div class="task-priority">Tinggi</div>
            </div>

            <div style="margin-bottom: 1rem;">
                <small style="color: #6c757d;">Progress: 1/3 selesai (33%)</small>
                <div style="background: #e9ecef; height: 8px; border-radius: 4px; margin-top: 0.5rem;">
                    <div style="background: #667eea; height: 100%; width: 33%; border-radius: 4px;"></div>
                </div>
            </div>

            <div class="subtasks">
                <div class="subtask-item">
                    <input class="subtask-checkbox" type="checkbox" checked disabled>
                    <span class="subtask-text" style="text-decoration: line-through; color: #6c757d;">
                        Instalasi XAMPP dan editor
                    </span>
                </div>
                <div class="subtask-item">
                    <input class="subtask-checkbox" type="checkbox" disabled>
                    <span class="subtask-text">
                        Belajar struktur dasar PHP
                    </span>
                </div>
                <div class="subtask-item">
                    <input class="subtask-checkbox" type="checkbox" disabled>
                    <span class="subtask-text">
                        Membuat form input sederhana
                    </span>
                </div>
            </div>

            <div class="task-actions">
                <button class="btn btn-primary" disabled>Update</button>
                <button class="btn btn-warning" disabled>Edit</button>
                <button class="btn btn-danger" disabled>Hapus</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
