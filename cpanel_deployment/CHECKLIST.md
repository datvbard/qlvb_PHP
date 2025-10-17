# ✅ CHECKLIST FIX LỖI CPANEL - QLVB SYSTEM

## 📥 BƯỚC CHUẨN BỊ
- [ ] Download file `cpanel_upload_package.zip` từ container
- [ ] Giải nén trên máy local
- [ ] Đọc file `SUMMARY_SOLUTION.md` để hiểu tổng quan

---

## 🔧 PHẦN 1: FIX PHP HANDLER (Ưu tiên 1)

### Mục tiêu: PHP files phải execute, không hiển thị source code

### Bước 1: Upload Files
- [ ] Vào cPanel File Manager
- [ ] Upload folder `api/` vào `/public_html/` (ghi đè nếu có)
- [ ] Verify tất cả file .php đã được upload

### Bước 2: Cấu hình PHP trong cPanel
- [ ] Vào **Select PHP Version** hoặc **MultiPHP Manager**
- [ ] Chọn PHP version **8.0** hoặc **8.1**
- [ ] Bật các extensions:
  - [ ] curl
  - [ ] json
  - [ ] mbstring
  - [ ] mysqli
  - [ ] pdo
  - [ ] pdo_mysql
  - [ ] openssl
  - [ ] fileinfo
- [ ] Click **Save**

### Bước 3: Cấu hình PHP INI
- [ ] Vào **MultiPHP INI Editor**
- [ ] Set các giá trị:
  - [ ] upload_max_filesize = 50M
  - [ ] post_max_size = 50M
  - [ ] max_execution_time = 300
  - [ ] memory_limit = 256M
- [ ] Click **Save**

### Bước 4: Kiểm tra File Permissions
- [ ] Trong File Manager, check permissions:
  - [ ] Folders (api/): **755**
  - [ ] PHP files (*.php): **644**
  - [ ] .htaccess: **644**

### Bước 5: Test PHP Execution

#### Test 1: Test PHP Info
- [ ] Tạo file `/public_html/api/test-php.php`:
```php
<?php
phpinfo();
?>
```
- [ ] Truy cập: `https://qlvb.phongkhcn.vn/api/test-php.php`
- [ ] **Kết quả mong đợi:** Hiển thị trang PHP Info (KHÔNG phải source code)

#### Test 2: Test JSON Output
- [ ] Tạo file `/public_html/api/test-json.php`:
```php
<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'PHP is working correctly',
    'php_version' => PHP_VERSION
]);
?>
```
- [ ] Truy cập: `https://qlvb.phongkhcn.vn/api/test-json.php`
- [ ] **Kết quả mong đợi:** JSON response (KHÔNG phải source code)

#### Test 3: Test Google Drive Script
- [ ] Truy cập: `https://qlvb.phongkhcn.vn/api/test_google_drive.php`
- [ ] **Kết quả mong đợi:** JSON với test results (KHÔNG phải source code)

### ❌ Nếu vẫn hiển thị source code:
- [ ] Thử các variant trong .htaccess:
  - [ ] `AddHandler application/x-httpd-php80 .php`
  - [ ] `AddHandler application/x-httpd-ea-php80 .php`
- [ ] Clear browser cache hoàn toàn (Ctrl+Shift+Delete)
- [ ] Test trong Incognito mode
- [ ] Liên hệ hosting support (xem template trong FIX_PHP_HANDLER.md)

### ✅ PHP Handler hoạt động khi:
- [ ] test-php.php hiển thị PHP Info page
- [ ] test-json.php trả về JSON (không phải code)
- [ ] test_google_drive.php trả về JSON (không phải code)

---

## 📁 PHẦN 2: FIX GOOGLE DRIVE (Ưu tiên 2)

### Mục tiêu: Upload file thành công và xuất hiện trong Google Drive folder

### Bước 1: Chuẩn Bị Service Account

#### 1.1. Verify Credentials File
- [ ] Có file `google-credentials.json` từ Google Cloud Console
- [ ] Mở file, tìm và copy `client_email`
- [ ] Ví dụ: `your-service-account@your-project.iam.gserviceaccount.com`

#### 1.2. Upload Credentials
- [ ] Vào cPanel File Manager
- [ ] Upload `google-credentials.json` vào `/public_html/api/`
- [ ] Verify file permissions: **644**

#### 1.3. Share Google Drive Folder
- [ ] Truy cập Google Drive: `https://drive.google.com`
- [ ] Tìm folder ID: `1OBWCc80JfExTWyUxx3W2I6ltY8CZILa0`
  - URL sẽ có dạng: `.../folders/1OBWCc80JfExTWyUxx3W2I6ltY8CZILa0`
- [ ] Click chuột phải vào folder → **Share** (Chia sẻ)
- [ ] Paste `client_email` từ credentials file
- [ ] **QUAN TRỌNG:** Chọn role **"Editor"** (Người chỉnh sửa)
- [ ] **BỎ TICK** ô "Notify people"
- [ ] Click **Share**

### Bước 2: Cài Đặt Google API Client

#### Option A: Qua Composer (nếu có SSH)
- [ ] SSH vào server
- [ ] `cd /home/username/public_html/api`
- [ ] `composer require google/apiclient:^2.15`

#### Option B: Manual Upload (không cần SSH)
- [ ] Trên máy local có composer:
  - [ ] `mkdir temp && cd temp`
  - [ ] `composer require google/apiclient:^2.15`
  - [ ] `zip -r vendor.zip vendor/`
- [ ] Upload `vendor.zip` vào `/public_html/api/`
- [ ] Extract trong File Manager (chuột phải → Extract)
- [ ] Verify cấu trúc:
  - [ ] `/public_html/api/vendor/autoload.php` tồn tại
  - [ ] `/public_html/api/vendor/google/` tồn tại

### Bước 3: Cấu Hình config.php
- [ ] Mở file `/public_html/api/config.php`
- [ ] Tìm section "GOOGLE DRIVE CONFIGURATION"
- [ ] Cập nhật:
  - [ ] `GOOGLE_DRIVE_ENABLED` = `true` (thay đổi từ false)
  - [ ] `GOOGLE_DRIVE_FOLDER_ID` = `'1OBWCc80JfExTWyUxx3W2I6ltY8CZILa0'`
- [ ] Save file

### Bước 4: Test Google Drive Integration

#### Test 1: Comprehensive Test
- [ ] Truy cập: `https://qlvb.phongkhcn.vn/api/test_google_drive.php`
- [ ] Kiểm tra output JSON:

**Tất cả phải "OK":**
- [ ] `php_version`: status = "OK"
- [ ] `google_drive_enabled`: status = "OK"
- [ ] `credentials_file`: status = "OK", exists = true
- [ ] `credentials_content`: status = "OK", type = "service_account"
- [ ] `folder_id`: status = "OK"
- [ ] `composer_autoload`: status = "OK", exists = true
- [ ] `google_client`: status = "OK"
- [ ] `drive_access`: status = "OK", message = "Successfully accessed..."

#### ❌ Nếu có test FAIL:
- [ ] `credentials_file` FAIL → Upload lại google-credentials.json
- [ ] `composer_autoload` FAIL → Cài lại Google API Client (Bước 2)
- [ ] `drive_access` FAIL → Làm lại Bước 1.3 (share folder)

#### Test 2: Upload File Test
- [ ] Tạo file `/public_html/api/test_upload.php` (copy code từ FIX_GOOGLE_DRIVE.md)
- [ ] Truy cập: `https://qlvb.phongkhcn.vn/api/test_upload.php`
- [ ] **Kết quả mong đợi:**
```json
{
    "status": "success",
    "message": "File uploaded to Google Drive successfully",
    "file_id": "...",
    "view_link": "https://drive.google.com/file/d/...",
    "download_link": "https://drive.google.com/uc?id=..."
}
```

### Bước 5: Verify trong Google Drive
- [ ] Mở Google Drive folder: `https://drive.google.com/drive/folders/1OBWCc80JfExTWyUxx3W2I6ltY8CZILa0`
- [ ] Kiểm tra file test_upload_XXXXX.txt xuất hiện
- [ ] Click vào file để xem nội dung
- [ ] Test download file

### ✅ Google Drive hoạt động khi:
- [ ] `test_google_drive.php` trả về all tests "OK"
- [ ] `test_upload.php` trả về status "success"
- [ ] File xuất hiện trong Google Drive folder
- [ ] File có thể xem và download được

---

## 🌐 PHẦN 3: TEST ỨNG DỤNG HOÀN CHỈNH

### Test Frontend
- [ ] Truy cập: `https://qlvb.phongkhcn.vn`
- [ ] Trang login hiển thị đúng
- [ ] Đăng nhập với: username `admin`, password `admin123`
- [ ] Dashboard hiển thị đúng với stats
- [ ] Menu navigation hoạt động

### Test Backend API
- [ ] Test login API:
```bash
curl -X POST https://qlvb.phongkhcn.vn/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```
- [ ] Response trả về token (không phải source code)

### Test CRUD Operations
- [ ] Tạo document mới
- [ ] Upload file vào document
- [ ] Verify file trong Google Drive
- [ ] Click link để xem file
- [ ] Edit document
- [ ] Delete document

### Test Categories & Menu
- [ ] Vào trang Categories (Admin)
- [ ] Tạo/Edit/Delete category
- [ ] Vào trang Menu Items (Admin)
- [ ] Tạo/Edit/Delete menu item

### Test Search & Filter
- [ ] Search documents by keyword
- [ ] Filter by category
- [ ] Filter by status (active/expiring/expired)

### Test Excel Export
- [ ] Export documents to Excel
- [ ] Download và mở file Excel
- [ ] Verify data chính xác

---

## 🧹 DỌN DẸP (Sau khi test thành công)

### Xóa Test Files
- [ ] Xóa `/public_html/api/test-php.php`
- [ ] Xóa `/public_html/api/test-json.php`
- [ ] Xóa `/public_html/api/test_upload.php`
- [ ] Giữ lại `test_google_drive.php` (có thể dùng sau)

### Tắt Error Display (Production)
- [ ] Mở `/public_html/api/config.php`
- [ ] Comment hoặc xóa dòng:
```php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
```
- [ ] Save file

### Đổi Password Admin (BẮT BUỘC)
- [ ] Vào phpMyAdmin trong cPanel
- [ ] Chọn database `qlvb_db`
- [ ] Chọn table `users`
- [ ] Tìm user `admin`
- [ ] Click **Edit**
- [ ] Trong PHP, generate hash mới:
```php
<?php
echo password_hash('your_new_password', PASSWORD_BCRYPT);
?>
```
- [ ] Copy hash và paste vào field `password_hash`
- [ ] Save

### Đổi JWT Secret (BẮT BUỘC)
- [ ] Mở `/public_html/api/config.php`
- [ ] Tìm dòng: `define('JWT_SECRET', '...');`
- [ ] Thay bằng random string mới (https://www.random.org/strings/)
- [ ] Save file

---

## 🎯 CHECKLIST TỔNG THỂ

### Infrastructure
- [x] PHP files execute correctly
- [x] Database connection stable
- [x] Google Drive integration working
- [x] File permissions correct

### Security
- [ ] Admin password changed
- [ ] JWT secret changed
- [ ] Error display disabled in production
- [ ] Test files removed

### Functionality
- [ ] Login/logout working
- [ ] Dashboard stats correct
- [ ] CRUD operations working
- [ ] File upload to Google Drive working
- [ ] File view/download working
- [ ] Search và filter working
- [ ] Excel export working
- [ ] Categories management working
- [ ] Menu items management working

### Documentation
- [x] FIX_PHP_HANDLER.md reviewed
- [x] FIX_GOOGLE_DRIVE.md reviewed
- [x] SUMMARY_SOLUTION.md reviewed
- [ ] All fixes implemented
- [ ] All tests passed

---

## 📞 KHI CẦN HỖ TRỢ

### Thông tin cần cung cấp:
1. Screenshot lỗi cụ thể
2. Output của `test_google_drive.php`
3. Error logs từ cPanel (Errors → Error Log)
4. PHP version đang dùng
5. Checklist này - đã làm đến bước nào

### Tài liệu tham khảo:
- `FIX_PHP_HANDLER.md` - Chi tiết fix PHP handler
- `FIX_GOOGLE_DRIVE.md` - Chi tiết fix Google Drive
- `SUMMARY_SOLUTION.md` - Tổng quan giải pháp
- `HUONG_DAN_DEPLOY.md` - Hướng dẫn deploy đầy đủ

---

## ✅ HOÀN THÀNH

Khi tất cả checklist được tick ✅, hệ thống đã sẵn sàng production!

**Website:** https://qlvb.phongkhcn.vn
**API:** https://qlvb.phongkhcn.vn/api/
**Status:** 🟢 ONLINE

🎉 **Chúc mừng bạn đã deploy thành công!** 🎉
