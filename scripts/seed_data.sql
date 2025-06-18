-- Insert default admin user
-- Password: admin123 (hashed with password_hash)
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@fantasktic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Insert sample regular user
-- Password: user123 (hashed with password_hash)
INSERT INTO users (username, email, password, full_name, role) VALUES 
('testuser', 'user@fantasktic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User', 'user');

-- Insert sample tasks for the test user
INSERT INTO tasks (user_id, title, deadline, priority) VALUES 
(2, 'Menyelesaikan Laporan Bulanan', '2025-01-25 17:00:00', 'Tinggi'),
(2, 'Meeting dengan Tim Marketing', '2025-01-22 14:00:00', 'Sedang'),
(2, 'Review Proposal Klien', '2025-01-28 10:00:00', 'Rendah');

-- Insert sample subtasks
INSERT INTO subtasks (task_id, text, sort_order) VALUES 
(1, 'Kumpulkan data penjualan', 1),
(1, 'Analisis performa tim', 2),
(1, 'Buat grafik dan chart', 3),
(1, 'Review dan finalisasi', 4),
(2, 'Siapkan agenda meeting', 1),
(2, 'Presentasi hasil campaign', 2),
(3, 'Baca proposal lengkap', 1),
(3, 'Buat catatan feedback', 2);
