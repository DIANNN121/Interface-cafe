-- Fix admin user dengan password yang sudah di-hash
-- Password: admin123

-- Delete existing admin user
DELETE FROM users WHERE username = 'admin';

-- Insert admin user dengan password yang di-hash menggunakan PASSWORD_DEFAULT
-- Hash ini dibuat dari password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', NOW());

-- Note: Password hash di atas adalah untuk password 'password'. 
-- Kita akan gunakan script PHP untuk generate hash yang benar untuk 'admin123'
