-- ===================================
-- DATABASE SCHEMA FOR QLVB SYSTEM
-- Document Management System
-- Domain: qlvb.phongkhcn.vn
-- ===================================

-- Create database (run this first if database doesn't exist)
-- CREATE DATABASE IF NOT EXISTS qlvb_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE qlvb_db;

-- ===================================
-- USERS TABLE
-- ===================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` VARCHAR(36) PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- CATEGORIES TABLE
-- ===================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id` VARCHAR(36) PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `color` VARCHAR(7) DEFAULT '#6B7280',
  `icon` VARCHAR(50) DEFAULT 'folder',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- DOCUMENTS TABLE
-- ===================================
CREATE TABLE IF NOT EXISTS `documents` (
  `id` VARCHAR(36) PRIMARY KEY,
  `document_number` VARCHAR(100) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `category_id` VARCHAR(36),
  `document_type` VARCHAR(100) NOT NULL,
  `issue_date` DATE NOT NULL,
  `expiry_date` DATE,
  `issuing_authority` VARCHAR(255),
  `status` ENUM('active', 'expiring', 'expired') DEFAULT 'active',
  `notes` TEXT,
  `created_by` VARCHAR(36),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_document_number (document_number),
  INDEX idx_category (category_id),
  INDEX idx_document_type (document_type),
  INDEX idx_status (status),
  INDEX idx_expiry_date (expiry_date),
  INDEX idx_created_by (created_by),
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- FILES TABLE (for Google Drive integration)
-- ===================================
CREATE TABLE IF NOT EXISTS `files` (
  `id` VARCHAR(36) PRIMARY KEY,
  `document_id` VARCHAR(36) NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `google_drive_id` VARCHAR(255),
  `google_drive_url` TEXT,
  `mime_type` VARCHAR(100),
  `size` BIGINT DEFAULT 0,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_document (document_id),
  INDEX idx_google_drive_id (google_drive_id),
  FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- MENU ITEMS TABLE
-- ===================================
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` VARCHAR(36) PRIMARY KEY,
  `label` VARCHAR(100) NOT NULL,
  `path` VARCHAR(255) NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'link',
  `order_index` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_order (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- INSERT DEFAULT DATA
-- ===================================

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`id`, `username`, `name`, `email`, `password_hash`, `role`) 
VALUES (
  UUID(),
  'admin',
  'Administrator',
  'admin@qlvb.vn',
  '$2y$10$rF9JqRZqZxgHkX9K1ZK9.ewBZqYGzQ5xPYGzQ5xPYGzQ5xPYGzQ5x',
  'admin'
) ON DUPLICATE KEY UPDATE username=username;

-- Note: The password hash above is a placeholder. 
-- After importing, update with actual password:
-- UPDATE users SET password_hash = PASSWORD_HASH('your_password') WHERE username = 'admin';

-- Insert default categories
INSERT INTO `categories` (`id`, `name`, `description`, `color`, `icon`) VALUES
  (UUID(), 'Giấy tờ chuyên môn', 'Các giấy tờ liên quan đến chuyên môn', '#3B82F6', 'briefcase'),
  (UUID(), 'Giấy tờ đảng', 'Các giấy tờ liên quan đến đảng', '#EF4444', 'flag'),
  (UUID(), 'Giấy tờ khác', 'Các giấy tờ khác', '#6B7280', 'folder')
ON DUPLICATE KEY UPDATE name=name;

-- Insert default menu items
INSERT INTO `menu_items` (`id`, `label`, `path`, `icon`, `order_index`) VALUES
  (UUID(), 'Trang chủ', '/dashboard', 'home', 1),
  (UUID(), 'Quản lý văn bản', '/documents', 'file-text', 2),
  (UUID(), 'Danh mục', '/categories', 'folder', 3)
ON DUPLICATE KEY UPDATE label=label;

-- ===================================
-- CREATE TRIGGERS FOR AUTO STATUS UPDATE
-- ===================================

DELIMITER $$

CREATE TRIGGER `update_document_status_before_insert`
BEFORE INSERT ON `documents`
FOR EACH ROW
BEGIN
  IF NEW.expiry_date IS NOT NULL THEN
    IF NEW.expiry_date < CURDATE() THEN
      SET NEW.status = 'expired';
    ELSEIF DATEDIFF(NEW.expiry_date, CURDATE()) <= 7 THEN
      SET NEW.status = 'expiring';
    ELSE
      SET NEW.status = 'active';
    END IF;
  END IF;
END$$

CREATE TRIGGER `update_document_status_before_update`
BEFORE UPDATE ON `documents`
FOR EACH ROW
BEGIN
  IF NEW.expiry_date IS NOT NULL THEN
    IF NEW.expiry_date < CURDATE() THEN
      SET NEW.status = 'expired';
    ELSEIF DATEDIFF(NEW.expiry_date, CURDATE()) <= 7 THEN
      SET NEW.status = 'expiring';
    ELSE
      SET NEW.status = 'active';
    END IF;
  END IF;
END$$

DELIMITER ;

-- ===================================
-- CREATE VIEW FOR DOCUMENT STATISTICS
-- ===================================

CREATE OR REPLACE VIEW `document_stats` AS
SELECT 
  COUNT(*) as total,
  SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
  SUM(CASE WHEN status = 'expiring' THEN 1 ELSE 0 END) as expiring,
  SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired
FROM documents;

-- ===================================
-- INDEXES FOR PERFORMANCE
-- ===================================

-- Additional composite indexes for common queries
ALTER TABLE `documents` 
ADD INDEX idx_status_expiry (status, expiry_date);

ALTER TABLE `documents` 
ADD INDEX idx_category_status (category_id, status);

ALTER TABLE `files` 
ADD INDEX idx_document_uploaded (document_id, uploaded_at);

-- ===================================
-- PERMISSIONS & SECURITY
-- ===================================

-- Grant appropriate permissions to the database user
-- GRANT SELECT, INSERT, UPDATE, DELETE ON qlvb_db.* TO 'qlvb_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ===================================
-- MAINTENANCE QUERIES (Run periodically)
-- ===================================

-- Update all document statuses based on current date
-- UPDATE documents 
-- SET status = CASE
--   WHEN expiry_date < CURDATE() THEN 'expired'
--   WHEN DATEDIFF(expiry_date, CURDATE()) <= 7 THEN 'expiring'
--   ELSE 'active'
-- END
-- WHERE expiry_date IS NOT NULL;

-- Clean up orphaned files (files without documents)
-- DELETE FROM files WHERE document_id NOT IN (SELECT id FROM documents);

-- ===================================
-- BACKUP RECOMMENDATIONS
-- ===================================

-- Run daily backup using cPanel or command:
-- mysqldump -u username -p qlvb_db > backup_$(date +%Y%m%d).sql

-- ===================================
-- COMPLETION MESSAGE
-- ===================================

SELECT 'Database schema created successfully!' as status,
       (SELECT COUNT(*) FROM users) as users_count,
       (SELECT COUNT(*) FROM categories) as categories_count,
       (SELECT COUNT(*) FROM documents) as documents_count,
       (SELECT COUNT(*) FROM files) as files_count,
       (SELECT COUNT(*) FROM menu_items) as menu_items_count;
