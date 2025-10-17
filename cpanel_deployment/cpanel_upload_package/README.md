# 📦 PACKAGE UPLOAD CPANEL - QLVB System

## 📋 Nội dung package này:

### 1. Backend API (folder `api/`)
- Tất cả file PHP backend
- File `.htaccess` đã được cập nhật với PHP handler

### 2. Documentation
- `FIX_PHP_HANDLER.md` - Hướng dẫn fix lỗi PHP handler
- `FIX_GOOGLE_DRIVE.md` - Hướng dẫn fix Google Drive integration
- `HUONG_DAN_DEPLOY.md` - Hướng dẫn deploy đầy đủ
- `database.sql` - Database schema

## 🔧 HƯỚNG DẪN SỬ DỤNG:

### Bước 1: Upload Backend
1. Vào cPanel File Manager
2. Navigate đến `/public_html/`
3. Upload toàn bộ folder `api/` (ghi đè nếu đã tồn tại)

### Bước 2: Cấu hình
1. Edit file `/public_html/api/config.php`
2. Cập nhật thông tin database
3. Cấu hình Google Drive (nếu cần)

### Bước 3: Fix PHP Handler
1. Mở file `FIX_PHP_HANDLER.md`
2. Làm theo từng bước chi tiết
3. Test bằng file `test_google_drive.php`

### Bước 4: Fix Google Drive
1. Mở file `FIX_GOOGLE_DRIVE.md`
2. Làm theo hướng dẫn chi tiết
3. Share folder với service account
4. Upload credentials file

## ✅ CHECKLIST

- [ ] Database đã import
- [ ] Backend files đã upload
- [ ] config.php đã cấu hình
- [ ] PHP handler đã fix (test-php.php hoạt động)
- [ ] Google Drive đã cấu hình (nếu cần)
- [ ] Test API endpoints thành công

## 📞 Hỗ trợ

Nếu gặp vấn đề, kiểm tra:
1. Error logs trong cPanel
2. File permissions (755 cho folders, 644 cho files)
3. PHP version (8.0 hoặc 8.1)

---

**Chúc bạn deploy thành công! 🎉**
