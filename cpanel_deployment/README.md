# 📦 PACKAGE DEPLOY CPANEL - HỆ THỐNG QUẢN LÝ VĂN BẢN
## Domain: qlvb.phongkhcn.vn

---

## 📂 CẤU TRÚC PACKAGE

```
cpanel_deployment/
├── README.md                    # File này
├── HUONG_DAN_DEPLOY.md         # Hướng dẫn chi tiết từng bước
├── database.sql                 # MySQL database schema
├── api/                         # Backend PHP
│   ├── config.php              # Cấu hình chính
│   ├── auth.php                # Authentication API
│   ├── categories.php          # Categories CRUD
│   ├── menu.php                # Menu CRUD
│   ├── documents.php           # Documents CRUD
│   ├── files.php               # File upload/download
│   └── .htaccess               # URL rewriting
└── google_drive_config.md      # Hướng dẫn Google Drive
```

---

## 🚀 QUICK START

### Bước 1: Import Database
```sql
1. Tạo database trong cPanel: qlvb_db
2. Import file database.sql vào phpMyAdmin
```

### Bước 2: Upload Backend
```bash
1. Upload folder api/ vào /public_html/api/
2. Edit api/config.php - cập nhật thông tin database
```

### Bước 3: Build & Upload Frontend
```bash
# Trong máy local
cd /app/frontend
npm run build

# Upload nội dung folder build/ vào /public_html/
```

### Bước 4: Test
```
Truy cập: https://qlvb.phongkhcn.vn
Login: admin / admin123
```

---

## 📋 YÊU CẦU HỆ THỐNG

### cPanel Requirements:
- ✅ PHP 7.4+ (khuyến nghị 8.0+)
- ✅ MySQL 5.7+
- ✅ mod_rewrite enabled
- ✅ 50MB disk space tối thiểu
- ✅ SSL certificate (HTTPS)

### Optional:
- Composer (cho PHPSpreadsheet xuất Excel)
- Google Drive API (cho upload files)

---

## 🔑 THÔNG TIN ĐĂNG NHẬP MẶC ĐỊNH

```
Username: admin
Password: admin123
```

**⚠️ QUAN TRỌNG: Đổi password ngay sau khi deploy!**

---

## 🎯 TÍNH NĂNG CHÍNH

### ✅ Đã Implement:
- Xác thực JWT
- CRUD Văn bản
- Quản trị Danh mục (Chuyên môn/Đảng)
- Quản trị Menu nghiệp vụ
- Upload files (Google Drive ready)
- Tìm kiếm & Lọc
- Xuất Excel
- Thống kê Dashboard
- Phân quyền Admin/User
- Responsive Design

---

## 📊 DATABASE SCHEMA

### Tables:
1. **users** - Quản lý người dùng
2. **categories** - Danh mục văn bản
3. **menu_items** - Menu nghiệp vụ
4. **documents** - Văn bản chính
5. **files** - File đính kèm

### Views:
- **document_stats** - Thống kê tự động

### Stored Procedures:
- **update_document_status()** - Cập nhật trạng thái văn bản

### Events:
- **daily_status_update** - Chạy tự động mỗi ngày

---

## 🔧 API ENDPOINTS

### Authentication:
```
POST   /api/auth/register
POST   /api/auth/login
GET    /api/auth/me
```

### Categories:
```
GET    /api/categories
GET    /api/categories/type/{type}
POST   /api/categories
PUT    /api/categories/{id}
DELETE /api/categories/{id}
```

### Menu:
```
GET    /api/menu-items
POST   /api/menu-items
PUT    /api/menu-items/{id}
DELETE /api/menu-items/{id}
```

### Documents:
```
GET    /api/documents
GET    /api/documents/stats
GET    /api/documents/export
POST   /api/documents
PUT    /api/documents/{id}
DELETE /api/documents/{id}
```

### Files:
```
POST   /api/documents/{id}/upload
GET    /api/documents/{id}/files
DELETE /api/files/{id}
```

---

## 🔐 BẢO MẬT

### Đã Implement:
- ✅ Password hashing (bcrypt)
- ✅ JWT authentication
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS protection
- ✅ CORS configuration
- ✅ Role-based access control

### Cần Làm Sau Deploy:
- [ ] Đổi admin password
- [ ] Đổi JWT secret
- [ ] Setup SSL certificate
- [ ] Enable HTTPS redirect
- [ ] Backup database thường xuyên

---

## 🌐 DOMAIN CONFIGURATION

### Domain: qlvb.phongkhcn.vn

Đảm bảo:
1. DNS A record trỏ về IP của hosting
2. SSL certificate đã cài đặt
3. HTTPS redirect enabled
4. www/non-www redirect configured

---

## 📱 RESPONSIVE DESIGN

Website hoạt động tốt trên:
- ✅ Desktop (1920px+)
- ✅ Laptop (1366px - 1920px)
- ✅ Tablet (768px - 1366px)
- ✅ Mobile (320px - 768px)

---

## 🎨 TECHNOLOGY STACK

### Frontend:
- React 19
- React Router v7
- Tailwind CSS
- Shadcn/UI Components
- Axios
- date-fns

### Backend:
- PHP 8.0+
- MySQL 8.0+
- PDO
- JWT
- Google Drive API (optional)

---

## 📦 PACKAGE SIZE

```
Database:         ~50KB (empty)
Backend (API):    ~100KB
Frontend (build): ~2-3MB
Total:           ~3-4MB
```

---

## 🔄 UPDATE PROCESS

### Cập nhật Frontend:
```bash
cd /app/frontend
npm run build
# Upload build/ mới
```

### Cập nhật Backend:
```bash
# Upload file PHP mới
# Không cần restart, PHP auto-reload
```

### Database Migration:
```sql
-- Chạy SQL scripts trong phpMyAdmin
-- Backup trước khi update
```

---

## 📞 SUPPORT

### Tài liệu:
- `HUONG_DAN_DEPLOY.md` - Hướng dẫn chi tiết
- `google_drive_config.md` - Cấu hình Google Drive

### Troubleshooting:
- Check PHP error logs trong cPanel
- Check browser console cho frontend errors
- Check `/logs/error_*.log` cho application logs

---

## ✅ DEPLOYMENT CHECKLIST

### Pre-Deploy:
- [ ] cPanel account ready
- [ ] Domain DNS configured
- [ ] SSL certificate ready

### Deploy:
- [ ] Database created & imported
- [ ] Backend uploaded & configured
- [ ] Frontend built & uploaded
- [ ] .htaccess configured
- [ ] Permissions set

### Post-Deploy:
- [ ] Test all API endpoints
- [ ] Test frontend functionality
- [ ] Change admin password
- [ ] Setup backup schedule
- [ ] Configure Google Drive (optional)

---

## 📈 PERFORMANCE

### Optimizations:
- ✅ Gzip compression enabled
- ✅ Browser caching configured
- ✅ Database indexes optimized
- ✅ Lazy loading images
- ✅ Code splitting (React)
- ✅ Minified assets

### Expected Load Times:
- First Load: 2-3 seconds
- Subsequent: <1 second
- API Response: 50-200ms

---

## 🎓 USER ROLES

### Admin:
- Full access to all features
- Manage categories
- Manage menu
- Manage users
- Manage documents

### User:
- View and manage documents
- Cannot access admin features

---

## 📝 NOTES

1. **Database Prefix**: cPanel thường thêm prefix vào database name và username
2. **File Upload**: Giới hạn theo PHP upload_max_filesize (default 2MB)
3. **Session**: JWT không cần PHP session, stateless
4. **Timezone**: UTC, convert khi hiển thị
5. **Character Set**: UTF-8 (hỗ trợ tiếng Việt đầy đủ)

---

**Phiên bản**: 2.0.0  
**Ngày cập nhật**: 2025-10-04  
**Tương thích**: cPanel, MySQL, PHP 7.4+

---

**🎉 Sẵn sàng deploy lên qlvb.phongkhcn.vn!**
