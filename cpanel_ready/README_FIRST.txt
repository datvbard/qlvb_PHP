╔════════════════════════════════════════════════════╗
║   PACKAGE ĐÃ BUILD SẴN - KHÔNG CẦN NPM BUILD!    ║
║           Domain: qlvb.phongkhcn.vn               ║
╚════════════════════════════════════════════════════╝

🎉 BẠN KHÔNG CẦN BUILD TRÊN WINDOWS!

Package này đã được build sẵn trên server. Bạn chỉ cần:
1. Upload lên cPanel
2. Import database
3. Cấu hình config.php
4. Sử dụng ngay!

════════════════════════════════════════════════════

📦 NỘI DUNG PACKAGE:

✅ index.html              - Frontend đã build
✅ static/                 - JS, CSS, Images
✅ .htaccess              - URL rewriting
✅ api/                   - Backend PHP (9 files)
✅ database.sql           - MySQL schema
✅ HUONG_DAN_NHANH.txt    - Hướng dẫn chi tiết

════════════════════════════════════════════════════

🚀 QUICK START (3 BƯỚC):

BƯỚC 1: Import Database
    • cPanel > MySQL Databases > Tạo database
    • phpMyAdmin > Import database.sql

BƯỚC 2: Upload Files
    • File Manager > /public_html/
    • Upload TẤT CẢ files từ package này

BƯỚC 3: Cấu hình
    • Edit api/config.php
    • Cập nhật DB_NAME, DB_USER, DB_PASS
    • Lưu lại

🎯 XEM FILE: HUONG_DAN_NHANH.txt ĐỂ BIẾT CHI TIẾT!

════════════════════════════════════════════════════

🔑 THÔNG TIN ĐĂNG NHẬP:

URL: https://qlvb.phongkhcn.vn
Username: admin
Password: admin123

⚠️ ĐỔI PASSWORD SAU KHI LOGIN!

════════════════════════════════════════════════════

📱 CẤU TRÚC SAU KHI UPLOAD:

/public_html/
├── index.html           ← React app
├── .htaccess           ← URL routing
├── static/             ← Assets
│   ├── js/
│   ├── css/
│   └── media/
└── api/                ← Backend PHP
    ├── config.php      ← CẤU HÌNH TẠI ĐÂY!
    ├── auth.php
    ├── categories.php
    ├── menu.php
    ├── documents.php
    ├── files.php
    ├── upload.php
    ├── google_drive.php
    └── .htaccess

════════════════════════════════════════════════════

⚙️ YÊU CẦU SERVER:

✅ PHP 7.4 trở lên (khuyến nghị 8.0+)
✅ MySQL 5.7 trở lên
✅ mod_rewrite enabled
✅ SSL certificate (HTTPS)

════════════════════════════════════════════════════

🛠️ CẤU HÌNH QUAN TRỌNG:

File: api/config.php

ĐỔI DÒNG NÀY:
define('DB_NAME', 'qlvb_db');
define('DB_USER', 'root');
define('DB_PASS', '');

THÀNH (thêm PREFIX username):
define('DB_NAME', 'username_qlvb_db');
define('DB_USER', 'username_qlvb_user');
define('DB_PASS', 'your_password');

VÍ DỤ cPanel username là "mysite":
define('DB_NAME', 'mysite_qlvb_db');
define('DB_USER', 'mysite_qlvb_user');
define('DB_PASS', 'MySecurePass123');

════════════════════════════════════════════════════

📊 SIZE THÔNG TIN:

Frontend (build):     ~600 KB
Backend (API):        ~45 KB
Database:             ~50 KB
Total:               ~700 KB (rất nhẹ!)

════════════════════════════════════════════════════

✨ TÍNH NĂNG ĐẦY ĐỦ:

✅ Đăng ký / Đăng nhập
✅ Quản lý văn bản (CRUD)
✅ Quản lý danh mục Chuyên môn / Đảng
✅ Quản lý menu nghiệp vụ
✅ Upload file (Google Drive ready)
✅ Xuất Excel
✅ Tìm kiếm & Lọc
✅ Thống kê Dashboard
✅ Phân quyền Admin/User
✅ Responsive design

════════════════════════════════════════════════════

🔥 ƯU ĐIỂM PACKAGE NÀY:

✅ ĐÃ BUILD SẴN - không cần npm, node.js
✅ Nhẹ (~700KB) - upload nhanh
✅ Tối ưu cho cPanel hosting
✅ Dễ cấu hình - chỉ edit 1 file
✅ Hoạt động ngay - không cần thêm gì

════════════════════════════════════════════════════

📖 TÀI LIỆU:

📄 HUONG_DAN_NHANH.txt  - Hướng dẫn từng bước chi tiết
📄 README_FIRST.txt     - File này

════════════════════════════════════════════════════

🎯 TEST SAU KHI UPLOAD:

1. https://qlvb.phongkhcn.vn
   → Phải thấy trang login

2. Login với admin/admin123
   → Phải vào được dashboard

3. Test thêm văn bản
   → Phải lưu được

4. Test xuất Excel
   → Phải tải được file

════════════════════════════════════════════════════

⚠️ TROUBLESHOOTING:

Lỗi 500?
→ Check PHP version >= 7.4
→ Check .htaccess syntax

Lỗi Database?
→ Verify DB credentials
→ Nhớ thêm PREFIX username

Lỗi 404?
→ Check mod_rewrite enabled
→ Verify .htaccess uploaded

════════════════════════════════════════════════════

💡 MẸO:

• Sau khi upload, clear browser cache (Ctrl+Shift+Del)
• Dùng Incognito mode để test
• Check browser console (F12) nếu có lỗi
• Xem PHP error log trong cPanel nếu cần

════════════════════════════════════════════════════

🆘 SUPPORT:

Gặp vấn đề? Check:
1. HUONG_DAN_NHANH.txt
2. PHP error log trong cPanel
3. Browser console (F12)
4. Hosting support

════════════════════════════════════════════════════

🎊 CHÚC MỪNG - BẠN ĐÃ SẴN SÀNG DEPLOY!

Chỉ cần làm theo HUONG_DAN_NHANH.txt
Website sẽ hoạt động trong 10 phút!

Good luck! 🚀

════════════════════════════════════════════════════
