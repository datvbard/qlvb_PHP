# 🔧 HƯỚNG DẪN SỬA LỖI PHP HANDLER TRÊN CPANEL

## ❌ Vấn Đề
Khi truy cập file PHP (ví dụ: `https://qlvb.phongkhcn.vn/api/test_google_drive.php`), trình duyệt hiển thị **mã nguồn PHP thô** thay vì **thực thi code PHP** và trả về kết quả JSON.

### Nguyên nhân:
- PHP Handler chưa được cấu hình đúng
- Phiên bản PHP không được thiết lập
- File .htaccess có thể bị conflict

---

## ✅ GIẢI PHÁP

### 🔹 BƯỚC 1: Kiểm tra và Cấu hình PHP Version

1. **Đăng nhập cPanel** tại: `https://qlvb.phongkhcn.vn:2083`

2. **Tìm và mở "Select PHP Version"** hoặc **"MultiPHP Manager"**
   - Nếu không tìm thấy, tìm "Software" → "Select PHP Version"
   - Hoặc "Software" → "MultiPHP Manager"

3. **Chọn phiên bản PHP**
   - Chọn domain: `qlvb.phongkhcn.vn`
   - Chọn PHP version: **8.0** hoặc **8.1** (khuyến nghị)
   - Click **Apply**

4. **Kích hoạt các PHP Extensions cần thiết**
   - Trong "Select PHP Version", click vào **"PHP Extensions"**
   - Đảm bảo các extensions sau được bật (check ✓):
     - ✅ `curl`
     - ✅ `json`
     - ✅ `mbstring`
     - ✅ `mysqli`
     - ✅ `pdo`
     - ✅ `pdo_mysql`
     - ✅ `openssl`
     - ✅ `fileinfo`
   - Click **Save**

---

### 🔹 BƯỚC 2: Cấu hình PHP Handler

1. **Mở "MultiPHP INI Editor"** (hoặc "PHP Configuration")
   - Trong cPanel, tìm "Software" → "MultiPHP INI Editor"

2. **Chọn domain** `qlvb.phongkhcn.vn`

3. **Kiểm tra/Cập nhật các cài đặt quan trọng:**
   ```
   upload_max_filesize = 50M
   post_max_size = 50M
   max_execution_time = 300
   max_input_time = 300
   memory_limit = 256M
   ```

4. **Click Save**

---

### 🔹 BƯỚC 3: Kiểm tra .htaccess trong thư mục /api

1. **Vào File Manager** trong cPanel

2. **Navigate đến** `/public_html/api/`

3. **Mở file `.htaccess`** (click chuột phải → Edit)

4. **Thêm các dòng sau vào ĐẦU file** `.htaccess`:

```apache
# ===================================
# PHP HANDLER CONFIGURATION
# ===================================

# Force PHP execution for .php files
AddHandler application/x-httpd-php .php

# Alternative (nếu dòng trên không work):
# AddHandler application/x-httpd-php80 .php
# Hoặc:
# AddHandler application/x-httpd-ea-php80 .php

# Ensure PHP files are not downloaded
<FilesMatch "\.php$">
    SetHandler application/x-httpd-php
</FilesMatch>

# ===================================
# URL REWRITING FOR API
# ===================================

RewriteEngine On

# Enable CORS
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization"

# Handle OPTIONS requests
RewriteCond %{REQUEST_METHOD} OPTIONS
RewriteRule ^(.*)$ $1 [R=200,L]

# Route /api/auth/* to auth.php
RewriteRule ^auth/(.*)$ auth.php/$1 [L,QSA]

# Route /api/categories/* to categories.php
RewriteRule ^categories/(.*)$ categories.php/$1 [L,QSA]
RewriteRule ^categories$ categories.php [L,QSA]

# Route /api/menu-items/* to menu.php
RewriteRule ^menu-items/(.*)$ menu.php/$1 [L,QSA]
RewriteRule ^menu-items$ menu.php [L,QSA]

# Route /api/documents/* to documents.php
RewriteRule ^documents/([^/]+)/upload$ upload.php/$1 [L,QSA]
RewriteRule ^documents/([^/]+)/files$ upload.php/$1 [L,QSA]
RewriteRule ^documents/(.*)$ documents.php/$1 [L,QSA]
RewriteRule ^documents$ documents.php [L,QSA]

# Route /api/files/* to files.php
RewriteRule ^files/(.*)$ files.php/$1 [L,QSA]
```

5. **Save Changes**

---

### 🔹 BƯỚC 4: Kiểm tra File Permissions

1. **Trong File Manager**, chọn folder `/public_html/api/`

2. **Kiểm tra permissions:**
   - **Folders** (`api/`, `vendor/`, etc.): `755`
   - **PHP files** (*.php): `644`
   - **`.htaccess`**: `644`

3. **Để thay đổi permissions:**
   - Click chuột phải vào file/folder → **Permissions**
   - Nhập số tương ứng hoặc check boxes
   - Click **Change Permissions**

---

### 🔹 BƯỚC 5: Test PHP Execution

#### Test 1: Tạo file test đơn giản

1. Tạo file `/public_html/api/test-php.php`:

```php
<?php
phpinfo();
?>
```

2. Truy cập: `https://qlvb.phongkhcn.vn/api/test-php.php`

3. **Kết quả mong đợi:**
   - Hiển thị trang **PHP Info** với thông tin cấu hình PHP
   - **KHÔNG** hiển thị mã nguồn `<?php phpinfo(); ?>`

#### Test 2: Test JSON output

1. Tạo file `/public_html/api/test-json.php`:

```php
<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'PHP is working correctly',
    'php_version' => PHP_VERSION,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
```

2. Truy cập: `https://qlvb.phongkhcn.vn/api/test-json.php`

3. **Kết quả mong đợi:**
```json
{
    "status": "success",
    "message": "PHP is working correctly",
    "php_version": "8.0.x",
    "timestamp": "2025-01-XX XX:XX:XX"
}
```

#### Test 3: Test Google Drive

1. Truy cập: `https://qlvb.phongkhcn.vn/api/test_google_drive.php`

2. **Kết quả mong đợi:**
   - Hiển thị JSON với kết quả test Google Drive
   - **KHÔNG** hiển thị mã nguồn PHP

---

## 🔥 TROUBLESHOOTING

### ❌ Vấn đề: Vẫn hiển thị mã nguồn PHP

**Giải pháp:**

1. **Thử các variant của AddHandler:**
   - Mở `.htaccess` và thay đổi dòng `AddHandler`:
   
   ```apache
   # Thử từng dòng sau (uncomment từng dòng một):
   AddHandler application/x-httpd-php .php
   # AddHandler application/x-httpd-php80 .php
   # AddHandler application/x-httpd-ea-php80 .php
   # AddHandler application/x-httpd-php81 .php
   ```

2. **Kiểm tra mod_rewrite có enabled không:**
   - Tạo file test `/public_html/.htaccess` với nội dung:
   ```apache
   RewriteEngine On
   RewriteRule ^test$ test.html [L]
   ```
   - Nếu không work, liên hệ hosting support để bật mod_rewrite

3. **Clear browser cache và cookies:**
   - Ctrl + Shift + Delete (Chrome/Firefox)
   - Hoặc dùng Incognito/Private mode

4. **Kiểm tra PHP CGI mode:**
   - Một số hosting dùng PHP CGI thay vì Apache module
   - Trong `.htaccess`, thêm:
   ```apache
   Options +ExecCGI
   AddHandler php-cgi .php
   ```

---

### ❌ Vấn đề: 500 Internal Server Error

**Giải pháp:**

1. **Check Error Log trong cPanel:**
   - cPanel → **Errors** → **Error Log**
   - Xem error message cụ thể

2. **Disable các dòng trong .htaccess từng dòng một:**
   - Comment từng section để tìm dòng gây lỗi
   - Dùng `#` để comment

3. **Kiểm tra syntax PHP:**
   - Đảm bảo tất cả file PHP có `<?php` ở đầu
   - Đảm bảo không có BOM (Byte Order Mark) trong file

---

### ❌ Vấn đề: CORS Errors

**Giải pháp:**

1. **Thêm vào file `/public_html/api/.htaccess`:**
```apache
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
Header always set Access-Control-Allow-Credentials "true"
```

2. **Hoặc thêm vào đầu mỗi file PHP:**
```php
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
```

---

## 📞 Liên Hệ Hosting Support

Nếu sau khi thử tất cả các bước trên vẫn không được, hãy liên hệ hosting support và cung cấp thông tin:

```
Subject: PHP files showing source code instead of executing

Hi,

I'm experiencing an issue where PHP files on my domain (qlvb.phongkhcn.vn) 
are displaying source code instead of executing.

Issue:
- When I access https://qlvb.phongkhcn.vn/api/test_google_drive.php
- Browser shows raw PHP code instead of executing it

What I need:
1. Enable PHP execution for domain qlvb.phongkhcn.vn
2. Ensure mod_rewrite is enabled
3. Set PHP version to 8.0 or 8.1
4. Enable required PHP extensions (curl, json, mysqli, pdo, etc.)

Please help configure the correct PHP handler.

Thank you!
```

---

## ✅ CHECKLIST

- [ ] PHP Version đã set (8.0 hoặc 8.1)
- [ ] PHP Extensions đã bật (curl, json, mysqli, pdo, etc.)
- [ ] .htaccess trong /api đã có AddHandler directive
- [ ] File permissions đúng (755 cho folders, 644 cho files)
- [ ] Test file test-php.php và test-json.php
- [ ] Browser cache đã clear
- [ ] test_google_drive.php trả về JSON (không phải source code)

---

**Sau khi PHP handler hoạt động, chuyển sang fix Google Drive integration!** ✅
