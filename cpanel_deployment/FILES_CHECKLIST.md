# ✅ CHECKLIST FILES DEPLOY CPANEL

## 📂 DANH SÁCH FILES CẦN UPLOAD

### 1️⃣ Backend API Files (`/public_html/api/`)

**Core Files:**
- ✅ `config.php` - Database connection, JWT, utilities (485 dòng)
- ✅ `auth.php` - Authentication endpoints: register, login, me (110 dòng)
- ✅ `categories.php` - CRUD categories với admin check (145 dòng)
- ✅ `menu.php` - CRUD menu items (130 dòng)
- ✅ `documents.php` - CRUD documents + export Excel (200 dòng)
- ✅ `files.php` - Delete file endpoint (50 dòng)
- ✅ `upload.php` - Upload file handler (120 dòng)
- ✅ `google_drive.php` - Google Drive integration (90 dòng)
- ✅ `.htaccess` - API URL rewriting (30 dòng)

**Optional:**
- ⭕ `google-credentials.json` - Google Drive credentials (sau khi cấu hình)

**Total Backend:** 9 files cần thiết + 1 optional

---

### 2️⃣ Frontend Build Files (`/public_html/`)

**Root Files:**
- ✅ `index.html` - React app entry point
- ✅ `manifest.json` - PWA manifest
- ✅ `robots.txt` - SEO robots
- ✅ `favicon.ico` - Website icon
- ✅ `.htaccess` - Root URL rewriting cho React Router

**Folders:**
- ✅ `static/` - Folder chứa:
  - `static/js/` - JavaScript bundles
  - `static/css/` - CSS stylesheets
  - `static/media/` - Images và fonts

**Total Frontend:** ~5 files + 1 folder với nhiều assets

---

### 3️⃣ Database File

- ✅ `database.sql` - MySQL schema với dữ liệu mẫu (300+ dòng)

**Import vào phpMyAdmin, KHÔNG upload lên web**

---

### 4️⃣ Documentation Files

**Bao gồm trong package (tham khảo, không upload lên web):**
- ✅ `README.md` - Thông tin package
- ✅ `HUONG_DAN_DEPLOY.md` - Hướng dẫn chi tiết
- ✅ `google_drive_config.md` - Cấu hình Google Drive
- ✅ `FILES_CHECKLIST.md` - File này
- ✅ `DEPLOY_NOTES.txt` - Quick notes

---

## 📋 UPLOAD CHECKLIST

### Bước 1: Upload Backend API
```
☐ Tạo folder /public_html/api/
☐ Upload config.php
☐ Upload auth.php
☐ Upload categories.php
☐ Upload menu.php
☐ Upload documents.php
☐ Upload files.php
☐ Upload upload.php
☐ Upload google_drive.php
☐ Upload .htaccess vào /api/
```

### Bước 2: Cấu hình Backend
```
☐ Edit api/config.php
  ☐ Cập nhật DB_HOST (thường là 'localhost')
  ☐ Cập nhật DB_NAME (thường có prefix: username_qlvb_db)
  ☐ Cập nhật DB_USER (username_qlvb_user)
  ☐ Cập nhật DB_PASS (password của bạn)
  ☐ Đổi JWT_SECRET (random string)
```

### Bước 3: Upload Frontend
```
☐ Build frontend: npm run build
☐ Upload index.html vào /public_html/
☐ Upload manifest.json
☐ Upload robots.txt
☐ Upload favicon.ico
☐ Upload toàn bộ folder static/
☐ Upload .htaccess vào /public_html/
```

### Bước 4: Database Setup
```
☐ Tạo database trong cPanel
☐ Tạo database user
☐ Grant ALL PRIVILEGES
☐ Import database.sql vào phpMyAdmin
☐ Verify tables được tạo
```

### Bước 5: Set Permissions
```
☐ Folders: 755
  ☐ /public_html/
  ☐ /public_html/api/
  ☐ /public_html/static/
☐ Files: 644
  ☐ Tất cả .php files
  ☐ Tất cả .html files
  ☐ Tất cả .htaccess files
```

### Bước 6: Verify
```
☐ Test database connection
☐ Test API login endpoint
☐ Test frontend loads
☐ Test login works
☐ Test CRUD operations
☐ Test file permissions
```

---

## 🔍 VERIFY API FILES

### Test từng file API:

**1. Test config.php:**
```bash
# Không test trực tiếp, được include bởi files khác
```

**2. Test auth.php:**
```bash
curl -X POST https://qlvb.phongkhcn.vn/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

**3. Test categories.php:**
```bash
curl https://qlvb.phongkhcn.vn/api/categories \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**4. Test menu.php:**
```bash
curl https://qlvb.phongkhcn.vn/api/menu-items \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**5. Test documents.php:**
```bash
curl https://qlvb.phongkhcn.vn/api/documents \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 📦 FILES SIZE REFERENCE

```
config.php         ~15 KB
auth.php           ~4 KB
categories.php     ~5 KB
menu.php           ~4 KB
documents.php      ~7 KB
files.php          ~2 KB
upload.php         ~4 KB
google_drive.php   ~3 KB
.htaccess          ~1 KB
--------------------------
Total Backend:     ~45 KB

Frontend build:    ~2-3 MB
database.sql:      ~50 KB
```

---

## 🚨 COMMON ISSUES

### Issue: 500 Internal Server Error
**Check:**
- ☐ PHP version >= 7.4
- ☐ File permissions correct (644)
- ☐ .htaccess syntax correct
- ☐ mod_rewrite enabled

### Issue: Database Connection Failed
**Check:**
- ☐ Database exists
- ☐ User has privileges
- ☐ Credentials correct in config.php
- ☐ Database name has correct prefix

### Issue: 404 Not Found
**Check:**
- ☐ .htaccess uploaded
- ☐ mod_rewrite enabled in cPanel
- ☐ Files in correct directories

### Issue: CORS Errors
**Check:**
- ☐ CORS headers in .htaccess
- ☐ API URL correct in frontend
- ☐ HTTPS/HTTP matching

---

## ✅ FINAL CHECKLIST

```
☐ All backend files uploaded
☐ All frontend files uploaded
☐ Database imported
☐ config.php configured
☐ Permissions set
☐ .htaccess files in place
☐ Admin password changed
☐ JWT secret changed
☐ API endpoints tested
☐ Frontend tested
☐ Login tested
☐ CRUD tested
☐ SSL certificate installed
☐ HTTPS redirect enabled
☐ Backup configured
```

---

## 📞 SUPPORT

**Nếu thiếu file:**
- Kiểm tra lại folder `/app/cpanel_deployment/api/`
- Tất cả 9 files backend phải có đủ
- Verify không có lỗi syntax

**Nếu file không hoạt động:**
- Check PHP error log
- Check file permissions
- Verify database connection
- Test API endpoints riêng lẻ

---

**🎯 MỤC TIÊU: 14 files backend/frontend + 1 database = DEPLOY THÀNH CÔNG!**
