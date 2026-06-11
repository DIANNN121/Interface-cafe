-- Migration script for QR Code Guest Order System
-- Run this script to add table_qr_codes table

-- Create table_qr_codes table
CREATE TABLE IF NOT EXISTS `table_qr_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_number` (`table_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample tables (1-10)
INSERT INTO `table_qr_codes` (`table_number`, `table_name`, `is_active`) VALUES
('1', 'Meja 1', 1),
('2', 'Meja 2', 1),
('3', 'Meja 3', 1),
('4', 'Meja 4', 1),
('5', 'Meja 5', 1),
('6', 'Meja 6', 1),
('7', 'Meja 7', 1),
('8', 'Meja 8', 1),
('9', 'Meja 9', 1),
('10', 'Meja 10', 1);
