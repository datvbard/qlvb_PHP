-- ===================================
-- HỆ THỐNG QUẢN LÝ VĂN BẢN
-- MySQL Database Schema
-- Domain: qlvb.phongkhcn.vn
-- ===================================

-- Tạo database
CREATE DATABASE IF NOT EXISTS qlvb_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qlvb_db;

-- ===================================
-- BẢNG USERS - Quản lý người dùng
-- ===================================
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- BẢNG CATEGORIES - Danh mục văn bản
-- ===================================
CREATE TABLE categories (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('chuyen_mon', 'dang') NOT NULL,
    icon VARCHAR(10) DEFAULT '📄',
    `order` INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_order (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- BẢNG MENU_ITEMS - Menu nghiệp vụ
-- ===================================
CREATE TABLE menu_items (
    id VARCHAR(36) PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    path VARCHAR(255) NOT NULL,
    icon VARCHAR(10) DEFAULT '📋',
    `order` INT DEFAULT 0,
    parent_id VARCHAR(36) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (`order`),
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- BẢNG DOCUMENTS - Văn bản
-- ===================================
CREATE TABLE documents (
    id VARCHAR(36) PRIMARY KEY,
    code VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    category_id VARCHAR(36) NOT NULL,
    assignee VARCHAR(100) NOT NULL,
    expiry_date DATE NOT NULL,
    summary TEXT,
    status ENUM('active', 'expiring', 'expired') DEFAULT 'active',
    created_by VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_expiry (expiry_date),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(username) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- BẢNG FILES - File đính kèm
-- ===================================
CREATE TABLE files (
    id VARCHAR(36) PRIMARY KEY,
    document_id VARCHAR(36) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    google_drive_id VARCHAR(255) DEFAULT NULL,
    google_drive_url TEXT DEFAULT NULL,
    mime_type VARCHAR(100),
    size BIGINT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_document (document_id),
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- DỮ LIỆU MẪU
-- ===================================

-- Admin user (password: admin123)
INSERT INTO users (id, username, password_hash, name, email, role) VALUES
(UUID(), 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản Trị Viên', 'admin@qlvb.phongkhcn.vn', 'admin');

-- Danh mục Chuyên môn
INSERT INTO categories (id, name, type, icon, `order`) VALUES
(UUID(), 'Hợp đồng', 'chuyen_mon', '📄', 1),
(UUID(), 'Quyết định', 'chuyen_mon', '📋', 2),
(UUID(), 'Thông báo', 'chuyen_mon', '📢', 3),
(UUID(), 'Công văn', 'chuyen_mon', '📝', 4),
(UUID(), 'Báo cáo', 'chuyen_mon', '📊', 5);

-- Danh mục Đảng
INSERT INTO categories (id, name, type, icon, `order`) VALUES
(UUID(), 'Nghị quyết', 'dang', '🏛️', 1),
(UUID(), 'Chỉ thị', 'dang', '📌', 2),
(UUID(), 'Kết luận', 'dang', '✅', 3);

-- Menu mặc định
INSERT INTO menu_items (id, title, path, icon, `order`) VALUES
(UUID(), 'Quản lý văn bản', '/documents', '📄', 1),
(UUID(), 'Quản lý danh mục', '/admin/categories', '📂', 2),
(UUID(), 'Quản lý menu', '/admin/menu', '☰', 3);

-- ===================================
-- STORED PROCEDURES
-- ===================================

-- Procedure để tính toán status văn bản
DELIMITER $$

CREATE PROCEDURE update_document_status()
BEGIN
    UPDATE documents 
    SET status = CASE
        WHEN DATEDIFF(expiry_date, CURDATE()) < 0 THEN 'expired'
        WHEN DATEDIFF(expiry_date, CURDATE()) <= 7 THEN 'expiring'
        ELSE 'active'
    END;
END$$

DELIMITER ;

-- Tạo event để tự động update status mỗi ngày
CREATE EVENT IF NOT EXISTS daily_status_update
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO CALL update_document_status();

-- ===================================
-- VIEWS
-- ===================================

-- View để thống kê văn bản
CREATE VIEW document_stats AS
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'expiring' THEN 1 ELSE 0 END) as expiring,
    SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired
FROM documents;

-- ===================================
-- INDEXES OPTIMIZATION
-- ===================================

-- Full-text search index cho tìm kiếm văn bản
ALTER TABLE documents ADD FULLTEXT INDEX idx_search (code, title, summary);

-- ===================================
-- PERMISSIONS
-- ===================================

-- Tạo user cho ứng dụng (thay đổi password trong production)
-- CREATE USER 'qlvb_user'@'localhost' IDENTIFIED BY 'password_here';
-- GRANT ALL PRIVILEGES ON qlvb_db.* TO 'qlvb_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ===================================
-- BACKUP PROCEDURE
-- ===================================

DELIMITER $$

CREATE PROCEDURE backup_info()
BEGIN
    SELECT 
        'Database' as info_type,
        DATABASE() as current_database,
        NOW() as backup_time,
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM categories) as total_categories,
        (SELECT COUNT(*) FROM documents) as total_documents,
        (SELECT COUNT(*) FROM files) as total_files;
END$$

DELIMITER ;

-- ===================================
-- NOTES
-- ===================================
-- 1. Thay đổi password admin sau khi import
-- 2. Cấu hình Google Drive trong PHP config
-- 3. Backup database định kỳ
-- 4. Monitor performance với indexes
-- ===================================
