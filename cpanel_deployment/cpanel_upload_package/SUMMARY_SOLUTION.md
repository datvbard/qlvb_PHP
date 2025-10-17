# 📦 TÓM TẮT GIẢI PHÁP - FIX LỖI CPANEL

## 🎯 MỤC TIÊU
Sửa 2 lỗi chính sau khi deploy lên cPanel hosting:
1. **PHP Handler**: File PHP hiển thị mã nguồn thay vì thực thi
2. **Google Drive**: Upload file thành công nhưng không xuất hiện trong folder

---

## ✅ NHỮNG GÌ ĐÃ LÀM

### 1. Cập nhật PHP Handler Configuration
- ✅ File `/app/cpanel_deployment/api/.htaccess` đã được cập nhật
- ✅ Thêm directives để force PHP execution:
  ```apache
  AddHandler application/x-httpd-php .php
  <FilesMatch "\.php$">
      SetHandler application/x-httpd-php
  </FilesMatch>
  ```

### 2. Tạo Test Suite cho Google Drive
- ✅ File `test_google_drive.php` - test đầy đủ các thành phần:
  - PHP version check
  - Google Drive enabled status
  - Credentials file validation
  - Service account configuration
  - Folder ID verification
  - Google API Client installation
  - Drive folder access test

### 3. Tạo Hướng Dẫn Chi Tiết

#### 📄 FIX_PHP_HANDLER.md
Hướng dẫn **5 bước** chi tiết để fix PHP handler:
- Bước 1: Cấu hình PHP Version trong cPanel
- Bước 2: Cấu hình PHP Handler settings
- Bước 3: Cập nhật .htaccess
- Bước 4: Kiểm tra File Permissions
- Bước 5: Test với 3 file test khác nhau

**Troubleshooting section** bao gồm:
- Giải pháp khi vẫn hiển thị source code
- Fix lỗi 500 Internal Server Error
- Fix CORS errors
- Template email liên hệ hosting support

#### 📄 FIX_GOOGLE_DRIVE.md
Hướng dẫn **6 bước** chi tiết để fix Google Drive:
- Bước 1: Kiểm tra Service Account và Credentials
- Bước 2: Cài đặt Google API PHP Client
- Bước 3: Cấu hình config.php
- Bước 4: Test Google Drive Integration
- Bước 5: Kiểm tra File Permissions
- Bước 6: Verify Google Cloud Console settings

**Troubleshooting section** bao gồm:
- Fix "Insufficient permissions"
- Fix "File not found" hoặc "Invalid folder ID"
- Fix "Failed to load credentials"
- Fix "Class 'Google_Client' not found"
- Hướng dẫn enable logging và debugging

### 4. Database Schema
- ✅ Tạo file `database.sql` hoàn chỉnh với:
  - Tables: users, categories, documents, files, menu_items
  - Triggers: Auto-update document status
  - Views: Document statistics
  - Indexes: Performance optimization
  - Default data: Admin user, categories, menu items

### 5. Package Upload
- ✅ Script `PACKAGE_FOR_UPLOAD.sh` để tự động package
- ✅ File zip `cpanel_upload_package.zip` chứa:
  - Toàn bộ API files với .htaccess đã fix
  - Tất cả documentation files
  - Database schema
  - README hướng dẫn nhanh

---

## 📂 CẤU TRÚC FILES ĐÃ TẠO

```
/app/cpanel_deployment/
├── api/
│   ├── .htaccess (✅ UPDATED - PHP handler)
│   ├── test_google_drive.php (✅ NEW)
│   ├── config.php
│   ├── auth.php
│   ├── documents.php
│   ├── upload.php
│   ├── google_drive.php
│   └── ... (other PHP files)
├── FIX_PHP_HANDLER.md (✅ NEW - 5 steps guide)
├── FIX_GOOGLE_DRIVE.md (✅ NEW - 6 steps guide)
├── database.sql (✅ NEW - complete schema)
├── HUONG_DAN_DEPLOY.md (existing)
├── PACKAGE_FOR_UPLOAD.sh (✅ NEW - auto package script)
├── cpanel_upload_package/ (✅ NEW - unpacked files)
└── cpanel_upload_package.zip (✅ NEW - ready to upload)
```

---

## 🚀 HƯỚNG DẪN SỬ DỤNG CHO USER

### Bước 1: Download Package
```bash
# Package location trong container:
/app/cpanel_deployment/cpanel_upload_package.zip

# Bạn có thể download toàn bộ folder /app/cpanel_deployment về máy
```

### Bước 2: Upload lên cPanel
1. Giải nén `cpanel_upload_package.zip` trên máy local
2. Vào cPanel File Manager
3. Upload folder `api/` vào `/public_html/` (ghi đè nếu cần)

### Bước 3: Fix PHP Handler
1. Mở file `FIX_PHP_HANDLER.md`
2. Làm theo **5 bước** chi tiết
3. Test bằng các file:
   - `test-php.php` (phpinfo)
   - `test-json.php` (JSON output)
   - `test_google_drive.php` (Google Drive test)

**Kết quả mong đợi:**
- ✅ PHP files thực thi chính xác
- ✅ Trả về JSON response (không phải source code)

### Bước 4: Fix Google Drive
1. Mở file `FIX_GOOGLE_DRIVE.md`
2. Làm theo **6 bước** chi tiết:
   - Share folder `1OBWCc80JfExTWyUxx3W2I6ltY8CZILa0` với service account email
   - Upload `google-credentials.json` vào `/public_html/api/`
   - Cài đặt Google API Client (composer hoặc manual)
   - Cấu hình `config.php`
   - Test với `test_google_drive.php`
   - Verify upload thành công

**Kết quả mong đợi:**
- ✅ `test_google_drive.php` trả về all tests "OK"
- ✅ File upload xuất hiện trong Google Drive
- ✅ File có thể xem và download

---

## 🔍 TROUBLESHOOTING NHANH

### ❌ Vấn đề: PHP vẫn hiển thị source code
**Giải pháp:**
1. Thử các variant của AddHandler trong .htaccess:
   - `AddHandler application/x-httpd-php80 .php`
   - `AddHandler application/x-httpd-ea-php80 .php`
2. Check PHP version trong cPanel (phải là 8.0+)
3. Clear browser cache hoàn toàn
4. Liên hệ hosting support nếu vẫn không work

### ❌ Vấn đề: Google Drive test fail
**Giải pháp:**
1. Check `test_google_drive.php` output - xem test nào fail
2. Nếu `credentials_file` fail → upload lại credentials
3. Nếu `composer_autoload` fail → cài lại Google API Client
4. Nếu `drive_access` fail → check quyền share folder

### ❌ Vấn đề: File upload nhưng không thấy trong Drive
**Giải pháp:**
1. Verify service account email đã được share folder với role **Editor**
2. Check folder ID đúng: `1OBWCc80JfExTWyUxx3W2I6ltY8CZILa0`
3. Check logs: `/home/username/logs/error_*.log`
4. Run `test_upload.php` để test chi tiết

---

## ✅ CHECKLIST HOÀN THÀNH

### PHP Handler Fix:
- [ ] PHP version 8.0+ đã set trong cPanel
- [ ] PHP extensions enabled (curl, json, mysqli, pdo)
- [ ] .htaccess updated với PHP handler directives
- [ ] File permissions correct (755 folders, 644 files)
- [ ] `test-php.php` shows phpinfo (không phải source code)
- [ ] `test-json.php` returns JSON (không phải source code)

### Google Drive Fix:
- [ ] `google-credentials.json` uploaded và valid
- [ ] Service account email shared vào folder (role: Editor)
- [ ] Google API Client installed (vendor/autoload.php exists)
- [ ] `config.php` configured (ENABLED=true, folder ID set)
- [ ] `test_google_drive.php` all tests return "OK"
- [ ] `test_upload.php` upload thành công
- [ ] File xuất hiện trong Google Drive folder
- [ ] File có thể xem và download

---

## 📞 HỖ TRỢ

Nếu gặp vấn đề:

1. **Kiểm tra logs:**
   - cPanel → Errors → Error Log
   - Application logs: `/home/username/logs/error_*.log`

2. **Kiểm tra configuration:**
   - PHP version & extensions
   - Database connection trong `config.php`
   - File permissions

3. **Test từng bước:**
   - Test PHP execution trước
   - Test database connection
   - Test Google Drive sau cùng

4. **Liên hệ hosting support:**
   - Nếu PHP handler không work sau khi thử tất cả
   - Nếu cần enable mod_rewrite
   - Nếu cần cài composer

---

## 🎯 KẾT QUẢ MONG ĐỢI

Sau khi hoàn thành tất cả các bước:

✅ Website: `https://qlvb.phongkhcn.vn`
- Frontend load chính xác
- Login/logout hoạt động
- Dashboard hiển thị stats

✅ API Backend: `https://qlvb.phongkhcn.vn/api/`
- Tất cả endpoints hoạt động
- PHP files execute (không hiển thị source code)
- Database connection stable

✅ Google Drive Integration:
- Upload file thành công
- File xuất hiện trong folder
- File có thể xem và download
- Multiple files per document supported

✅ CRUD Operations:
- Create/Read/Update/Delete documents
- Categories management
- Menu items management
- Search và filter
- Excel export

---

## 📦 FILES CẦN DOWNLOAD

Download về máy từ container:
```
/app/cpanel_deployment/cpanel_upload_package.zip
```

Hoặc download từng file:
```
/app/cpanel_deployment/FIX_PHP_HANDLER.md
/app/cpanel_deployment/FIX_GOOGLE_DRIVE.md
/app/cpanel_deployment/database.sql
/app/cpanel_deployment/api/ (toàn bộ folder)
```

---

**Chúc bạn fix thành công! 🎉**

Nếu cần hỗ trợ thêm, hãy cung cấp:
1. Screenshot lỗi cụ thể
2. Output của `test_google_drive.php`
3. Error logs từ cPanel
4. PHP version và extensions enabled
