# HƯỚNG DẪN DEPLOY LÊN CPANEL
## Domain: qlvb.phongkhcn.vn

---

## 📋 CHUẨN BỊ

### Yêu cầu cPanel:
- ✅ PHP 7.4 trở lên (khuyến nghị 8.0+)
- ✅ MySQL 5.7 trở lên
- ✅ PHPMyAdmin
- ✅ mod_rewrite enabled
- ✅ Composer (optional, cho PHPSpreadsheet)

---

## 🗄️ BƯỚC 1: SETUP DATABASE

### 1.1. Tạo Database trong cPanel

1. Đăng nhập cPanel tại: `https://qlvb.phongkhcn.vn:2083`
2. Vào **MySQL® Databases**
3. Tạo database mới:
   - Database Name: `qlvb_db`
   - Click **Create Database**

### 1.2. Tạo MySQL User

1. Trong cùng trang MySQL® Databases
2. Scroll xuống phần **MySQL Users**
3. Tạo user mới:
   - Username: `qlvb_user`
   - Password: `[chọn password mạnh]`
   - Click **Create User**

### 1.3. Gán Quyền User

1. Scroll xuống **Add User To Database**
2. Chọn:
   - User: `qlvb_user`
   - Database: `qlvb_db`
3. Click **Add**
4. Chọn **ALL PRIVILEGES**
5. Click **Make Changes**

### 1.4. Import Database Schema

1. Vào **phpMyAdmin** từ cPanel
2. Chọn database `qlvb_db`
3. Click tab **Import**
4. Chọn file `database.sql`
5. Click **Go**

**Lưu ý**: Sau khi import, đổi password admin:
```sql
UPDATE users SET password_hash = '$2y$10$[your_new_hash]' WHERE username = 'admin';
```

---

## 📁 BƯỚC 2: UPLOAD FILES

### 2.1. Cấu trúc thư mục cPanel

```
/home/username/
├── public_html/              # Root website
│   ├── index.html           # React build (từ frontend)
│   ├── static/              # React static files
│   ├── manifest.json
│   ├── robots.txt
│   ├── .htaccess           # Main htaccess
│   └── api/                # PHP Backend
│       ├── config.php
│       ├── auth.php
│       ├── categories.php
│       ├── menu.php
│       ├── documents.php
│       ├── files.php
│       ├── upload.php
│       ├── google_drive.php
│       ├── .htaccess       # API htaccess
│       └── google-credentials.json (optional)
├── logs/                    # Application logs
└── uploads/                 # Uploaded files (temporary)
```

### 2.2. Upload Backend (API)

1. Vào **File Manager** trong cPanel
2. Navigate tới `/public_html/`
3. Tạo folder `api`
4. Upload tất cả files từ `/cpanel_deployment/api/` vào `/public_html/api/`
   - config.php
   - auth.php
   - categories.php
   - menu.php
   - documents.php
   - files.php
   - .htaccess

### 2.3. Cấu hình Database Connection

1. Edit file `/public_html/api/config.php`
2. Cập nhật thông tin database:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'username_qlvb_db');  // Thường có prefix username
define('DB_USER', 'username_qlvb_user');
define('DB_PASS', 'your_database_password');
```

**Lưu ý**: cPanel thường thêm prefix username vào database name và username.
Ví dụ: `myaccount_qlvb_db`, `myaccount_qlvb_user`

### 2.4. Tạo Main .htaccess

Tạo file `/public_html/.htaccess`:

```apache
# Enable Rewrite Engine
RewriteEngine On

# Redirect API requests to api folder
RewriteRule ^api/(.*)$ api/$1 [L,QSA]

# Redirect all other requests to index.html for React Router
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.html [L,QSA]

# Enable CORS
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization"

# Security Headers
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## ⚛️ BƯỚC 3: BUILD VÀ DEPLOY FRONTEND

### 3.1. Cập nhật .env cho Production

Trong máy local, tạo file `/app/frontend/.env.production`:

```env
REACT_APP_BACKEND_URL=https://qlvb.phongkhcn.vn
```

### 3.2. Build React App

Trong terminal local:

```bash
cd /app/frontend
npm run build
```

Hoặc:

```bash
yarn build
```

### 3.3. Upload Build Files

1. Sau khi build xong, folder `build/` sẽ được tạo
2. Upload toàn bộ nội dung trong `build/` vào `/public_html/`
   - index.html
   - static/ (folder)
   - manifest.json
   - robots.txt
   - favicon.ico

**Lưu ý**: KHÔNG upload toàn bộ folder `build`, chỉ upload NỘI DUNG bên trong.

### 3.4. Set Permissions

Trong File Manager, set permissions:
- Folders: 755
- Files: 644
- api/ folder: 755
- api/*.php: 644

---

## 🔧 BƯỚC 4: TEST & VERIFY

### 4.1. Test Database Connection

Tạo file test `/public_html/api/test-db.php`:

```php
<?php
require_once 'config.php';

try {
    $db = getDb();
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo json_encode([
        'status' => 'success',
        'users' => $result['count']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
```

Truy cập: `https://qlvb.phongkhcn.vn/api/test-db.php`

### 4.2. Test API Endpoints

```bash
# Test login
curl -X POST https://qlvb.phongkhcn.vn/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Test categories
curl https://qlvb.phongkhcn.vn/api/categories \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4.3. Test Frontend

1. Truy cập: `https://qlvb.phongkhcn.vn`
2. Kiểm tra login page hiển thị đúng
3. Đăng nhập với admin/admin123
4. Kiểm tra tất cả chức năng

---

## 🔐 BƯỚC 5: BẢO MẬT

### 5.1. Đổi Password Admin

```sql
-- Trong phpMyAdmin
UPDATE users 
SET password_hash = PASSWORD('new_secure_password') 
WHERE username = 'admin';
```

### 5.2. Cập nhật JWT Secret

Edit `/public_html/api/config.php`:

```php
define('JWT_SECRET', 'random-secure-string-here-change-this');
```

Generate random string: https://www.random.org/strings/

### 5.3. Disable Directory Listing

Thêm vào `/public_html/.htaccess`:

```apache
Options -Indexes
```

### 5.4. Protect Sensitive Files

Tạo `/public_html/api/.htaccess`:

```apache
# Protect config and credentials
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>

<Files "google-credentials.json">
    Order Allow,Deny
    Deny from all
</Files>
```

---

## 📊 BƯỚC 6: GOOGLE DRIVE (TÙY CHỌN)

### 6.1. Setup Google Drive

1. Làm theo hướng dẫn trong file `google_drive_config.md`
2. Download `google-credentials.json`
3. Upload vào `/public_html/api/`

### 6.2. Cấu hình

Edit `/public_html/api/config.php`:

```php
define('GOOGLE_DRIVE_ENABLED', true);
define('GOOGLE_CREDENTIALS_FILE', __DIR__ . '/google-credentials.json');
define('GOOGLE_DRIVE_FOLDER_ID', 'your_folder_id_here');
```

### 6.3. Install Google API PHP Client

Via SSH (nếu có):

```bash
cd /home/username/public_html/api
composer require google/apiclient
```

Hoặc upload manually từ: https://github.com/googleapis/google-api-php-client

---

## 🐛 TROUBLESHOOTING

### Lỗi 500 Internal Server Error

1. Check PHP error log:
   - cPanel > Errors > Error Log

2. Enable PHP error display (chỉ trong development):
   ```php
   // Thêm vào đầu config.php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

3. Check file permissions

### Lỗi Database Connection

1. Verify database credentials
2. Check if database exists
3. Verify user có quyền access database

### Lỗi 404 Not Found

1. Verify .htaccess có mod_rewrite
2. Check file paths
3. Verify RewriteBase nếu cần:
   ```apache
   RewriteBase /
   ```

### CORS Errors

1. Verify CORS headers trong .htaccess
2. Check API URL trong frontend .env

---

## 📝 MAINTENANCE

### Backup Database

1. Vào phpMyAdmin
2. Chọn database `qlvb_db`
3. Click **Export**
4. Chọn **Quick** method
5. Click **Go**

Schedule automatic backups trong cPanel > Backup Wizard

### Update Application

1. Build frontend mới
2. Upload files mới (ghi đè)
3. Clear browser cache

### Monitor Logs

Check logs tại:
- PHP Error Log: cPanel > Errors
- Application Log: `/home/username/logs/error_*.log`

---

## ✅ CHECKLIST HOÀN THÀNH

- [ ] Database created và imported
- [ ] Database user created và granted privileges
- [ ] Backend files uploaded
- [ ] config.php configured with database credentials
- [ ] Frontend built và uploaded
- [ ] .htaccess files configured
- [ ] File permissions set correctly
- [ ] Admin password changed
- [ ] JWT secret changed
- [ ] Test database connection
- [ ] Test API endpoints
- [ ] Test frontend login
- [ ] Test all CRUD operations
- [ ] Google Drive configured (optional)
- [ ] SSL certificate installed (https)
- [ ] Backup strategy setup

---

## 📞 HỖ TRỢ

Nếu gặp vấn đề:
1. Check error logs
2. Verify all configuration
3. Test API endpoints individually
4. Contact hosting support for server issues

---

**Chúc mừng! Hệ thống đã sẵn sàng tại: https://qlvb.phongkhcn.vn** 🎉
